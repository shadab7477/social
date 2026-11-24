<?php

namespace App\Livewire\Calendar;

use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Jobs\Publishing\PublishPostVariantJob;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ContentCalendar extends Component
{
    // Filter State
    public ?int $selectedClientId = null;
    public string $selectedPlatform = '';
    public string $selectedStatus = '';

    // Modal & Variant State
    public ?int $selectedVariantId = null;
    public bool $showDetailModal = false;
    public string $rescheduleDate = '';
    public string $rescheduleTime = '12:00';

    // Flash Feedback
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    public function mount(): void
    {
        $this->rescheduleDate = now()->addDay()->format('Y-m-d');
        $this->rescheduleTime = '10:00';
    }

    /**
     * Fetch calendar events formatted for FullCalendar.
     * Can be invoked from Livewire or via Alpine.
     */
    public function getEvents(string $start, string $end): array
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        $query = PostVariant::query()
            ->whereHas('post') // Enforces workspace multi-tenant scope automatically
            ->with(['post.client', 'socialProfile', 'mediaAssets'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_at', [$startDate, $endDate])
                  ->orWhereBetween('published_at', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->whereNull('scheduled_at')
                          ->whereNull('published_at')
                          ->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });

        if ($this->selectedClientId) {
            $query->whereHas('post', fn($q) => $q->where('client_id', $this->selectedClientId));
        }

        if ($this->selectedPlatform) {
            $query->whereHas('socialProfile', fn($q) => $q->where('platform', $this->selectedPlatform));
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        $variants = $query->get();

        return $variants->map(function (PostVariant $variant) {
            $eventDate = $variant->scheduled_at ?? $variant->published_at ?? $variant->created_at;
            $platform = $variant->socialProfile?->platform?->value ?? 'facebook';
            $status = $variant->status;

            // Determine border and background colors
            $colors = $this->resolveEventColors($platform, $status);

            return [
                'id' => (string) $variant->id,
                'title' => ($variant->socialProfile?->name ?? 'Social') . ': ' . Str::limit($variant->getEffectiveCaption(), 28),
                'start' => $eventDate->toIso8601String(),
                'allDay' => false,
                'backgroundColor' => $colors['background'],
                'borderColor' => $colors['border'],
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'variantId' => $variant->id,
                    'postId' => $variant->post_id,
                    'clientName' => $variant->post?->client?->name ?? 'General',
                    'profileName' => $variant->socialProfile?->name ?? 'Social Account',
                    'platform' => $platform,
                    'status' => $status->value,
                    'statusLabel' => $status->label(),
                    'caption' => $variant->getEffectiveCaption(),
                    'mediaCount' => $variant->mediaAssets->count(),
                    'thumbnailUrl' => $variant->mediaAssets->first()?->public_url,
                    'externalUrl' => $variant->external_post_url,
                    'errorMessage' => $variant->error_message,
                ],
            ];
        })->toArray();
    }

    /**
     * Open details modal for a clicked variant event.
     */
    public function openVariantModal(int $variantId): void
    {
        $variant = PostVariant::with(['post.client', 'socialProfile', 'mediaAssets', 'publishingRecord.attempts'])
            ->whereHas('post')
            ->find($variantId);

        if (!$variant) {
            return;
        }

        $this->selectedVariantId = $variant->id;
        $targetDate = $variant->scheduled_at ?? now()->addDay();
        $this->rescheduleDate = $targetDate->format('Y-m-d');
        $this->rescheduleTime = $targetDate->format('H:i');
        $this->showDetailModal = true;
    }

    public function closeVariantModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedVariantId = null;
    }

    /**
     * Reschedule variant from the modal form.
     */
    public function saveReschedule(): void
    {
        if (!$this->selectedVariantId) {
            return;
        }

        $this->validate([
            'rescheduleDate' => 'required|date',
            'rescheduleTime' => 'required|date_format:H:i',
        ]);

        $newDateTime = Carbon::parse($this->rescheduleDate . ' ' . $this->rescheduleTime);

        $variant = PostVariant::whereHas('post')->find($this->selectedVariantId);
        if (!$variant) {
            return;
        }

        $variant->update([
            'scheduled_at' => $newDateTime,
            'status' => PostStatus::SCHEDULED,
            'error_message' => null,
            'retry_count' => 0,
        ]);

        // Keep parent post in sync
        $post = $variant->post;
        if (in_array($post->status, [PostStatus::DRAFT, PostStatus::FAILED, PostStatus::SCHEDULED])) {
            $post->update([
                'scheduled_at' => $newDateTime,
                'status' => PostStatus::SCHEDULED,
            ]);
        }

        $this->feedbackMessage = "Post successfully rescheduled to " . $newDateTime->format('M d, Y h:i A');
        $this->feedbackType = 'success';
        $this->showDetailModal = false;

        $this->dispatch('calendar-refresh');
    }

    /**
     * Reschedule variant directly from FullCalendar drag & drop.
     */
    public function rescheduleVariantFromCalendar(int $variantId, string $newDateTimeString): void
    {
        $variant = PostVariant::whereHas('post')->find($variantId);
        if (!$variant) {
            return;
        }

        $newDateTime = Carbon::parse($newDateTimeString);

        $variant->update([
            'scheduled_at' => $newDateTime,
            'status' => PostStatus::SCHEDULED,
            'error_message' => null,
            'retry_count' => 0,
        ]);

        $variant->post->update([
            'scheduled_at' => $newDateTime,
            'status' => PostStatus::SCHEDULED,
        ]);

        $this->feedbackMessage = "Rescheduled to " . $newDateTime->format('M d, Y h:i A');
        $this->feedbackType = 'success';

        $this->dispatch('calendar-refresh');
    }

    /**
     * 1-Click Retry publishing for a failed post variant.
     */
    public function retryVariant(int $variantId): void
    {
        $variant = PostVariant::whereHas('post')->find($variantId);
        if (!$variant) {
            return;
        }

        $variant->update([
            'status' => PostStatus::QUEUED,
            'error_message' => null,
            'retry_count' => 0,
        ]);

        PublishPostVariantJob::dispatch($variant->id, (string) Str::uuid())
            ->onQueue('publishing');

        $this->feedbackMessage = "Publishing worker dispatched for retry!";
        $this->feedbackType = 'info';
        $this->showDetailModal = false;

        $this->dispatch('calendar-refresh');
    }

    /**
     * Helper to resolve styling based on platform and status.
     */
    protected function resolveEventColors(string $platform, PostStatus $status): array
    {
        if ($status === PostStatus::FAILED) {
            return ['background' => '#e11d48', 'border' => '#be123c']; // Rose / Red
        }

        if ($status === PostStatus::PUBLISHED) {
            return ['background' => '#059669', 'border' => '#047857']; // Emerald
        }

        return match ($platform) {
            'facebook' => ['background' => '#1877F2', 'border' => '#1565C0'],
            'instagram' => ['background' => '#E1306C', 'border' => '#C13584'],
            'linkedin' => ['background' => '#0A66C2', 'border' => '#084B8A'],
            'youtube' => ['background' => '#FF0000', 'border' => '#CC0000'],
            'tiktok' => ['background' => '#000000', 'border' => '#1F2937'],
            'pinterest' => ['background' => '#BD081C', 'border' => '#8C0615'],
            'x' => ['background' => '#0F1419', 'border' => '#000000'],
            'threads' => ['background' => '#101010', 'border' => '#000000'],
            'google_business' => ['background' => '#4285F4', 'border' => '#3367D6'],
            'bluesky' => ['background' => '#0285FF', 'border' => '#0267C7'],
            default => ['background' => '#4f46e5', 'border' => '#4338ca'],
        };
    }

    public function render()
    {
        $clients = Client::orderBy('name')->get();
        $selectedVariant = null;

        if ($this->selectedVariantId) {
            $selectedVariant = PostVariant::with(['post.client', 'socialProfile', 'mediaAssets', 'publishingRecord.attempts'])
                ->whereHas('post')
                ->find($this->selectedVariantId);
        }

        return view('livewire.calendar.content-calendar', [
            'clients' => $clients,
            'platforms' => SocialPlatform::cases(),
            'statuses' => PostStatus::cases(),
            'selectedVariant' => $selectedVariant,
        ]);
    }
}

