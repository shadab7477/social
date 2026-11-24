<?php

namespace App\Livewire\Posts;

use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Jobs\Publishing\PublishPostVariantJob;
use App\Models\Client;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PostList extends Component
{
    use WithPagination;

    // Filters & Search
    public string $statusFilter = 'all'; // all, scheduled, published, failed, pending_approval, draft
    public ?int $clientId = null;
    public string $platform = '';
    public string $search = '';

    // Modal inspection state
    public ?int $selectedPostId = null;
    public bool $showDetailModal = false;

    // Feedback Alert
    public ?string $feedbackMessage = null;
    public string $feedbackType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClientId(): void
    {
        $this->resetPage();
    }

    public function updatingPlatform(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function openDetailModal(int $postId): void
    {
        $post = Post::with(['client', 'author', 'variants.socialProfile', 'variants.mediaAssets', 'variants.publishingRecord.attempts'])
            ->find($postId);

        if (!$post) {
            return;
        }

        $this->selectedPostId = $post->id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedPostId = null;
    }

    /**
     * 1-Click retry for a specific failed variant.
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

        // Keep parent post in publishing or scheduled state
        $variant->post->update(['status' => PostStatus::PUBLISHING]);

        PublishPostVariantJob::dispatch($variant->id, (string) Str::uuid())
            ->onQueue('publishing');

        $this->feedbackMessage = "Retry job dispatched to worker for {$variant->socialProfile?->name}!";
        $this->feedbackType = 'info';
    }

    /**
     * Clone a post into a new draft.
     */
    public function clonePost(int $postId): void
    {
        $source = Post::with(['variants.mediaAssets'])->find($postId);
        if (!$source) {
            return;
        }

        $newPost = Post::create([
            'workspace_id' => $source->workspace_id,
            'client_id' => $source->client_id,
            'author_id' => Auth::id(),
            'title' => $source->title ? $source->title . ' (Copy)' : 'Cloned Post',
            'base_caption' => $source->base_caption,
            'destination_link' => $source->destination_link,
            'status' => PostStatus::DRAFT,
            'scheduled_at' => null,
            'timezone' => $source->timezone,
            'requires_approval' => false,
        ]);

        foreach ($source->variants as $var) {
            $newVar = PostVariant::create([
                'post_id' => $newPost->id,
                'social_profile_id' => $var->social_profile_id,
                'custom_caption' => $var->custom_caption,
                'platform_settings' => $var->platform_settings,
                'status' => PostStatus::DRAFT,
                'scheduled_at' => null,
            ]);

            // Copy media attachments
            foreach ($var->mediaAssets as $media) {
                $newVar->mediaAssets()->attach($media->id, [
                    'sort_order' => $media->pivot->sort_order,
                    'custom_alt_text' => $media->pivot->custom_alt_text,
                ]);
            }
        }

        $this->feedbackMessage = "Post successfully duplicated as a new draft!";
        $this->feedbackType = 'success';
    }

    /**
     * Delete or cancel a post and its variants.
     */
    public function deletePost(int $postId): void
    {
        $post = Post::with('variants')->find($postId);
        if (!$post) {
            return;
        }

        // Delete variants and soft delete post
        $post->variants()->delete();
        $post->delete();

        if ($this->selectedPostId === $postId) {
            $this->closeDetailModal();
        }

        $this->feedbackMessage = "Post and associated variants deleted.";
        $this->feedbackType = 'warning';
    }

    public function render()
    {
        $query = Post::query()
            ->with(['client', 'author', 'variants.socialProfile', 'variants.mediaAssets'])
            ->latest();

        // Status Filter
        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'failed') {
                $query->where(function ($q) {
                    $q->where('status', PostStatus::FAILED)
                      ->orWhereHas('variants', fn($v) => $v->where('status', PostStatus::FAILED));
                });
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // Client Filter
        if ($this->clientId) {
            $query->where('client_id', $this->clientId);
        }

        // Platform Filter
        if ($this->platform) {
            $query->whereHas('variants.socialProfile', fn($p) => $p->where('platform', $this->platform));
        }

        // Search Filter
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('base_caption', 'like', $searchTerm)
                  ->orWhere('title', 'like', $searchTerm)
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', $searchTerm));
            });
        }

        $posts = $query->paginate(12);
        $clients = Client::orderBy('name')->get();

        // Metrics for Tab Badges
        $metrics = [
            'all' => Post::count(),
            'scheduled' => Post::where('status', PostStatus::SCHEDULED)->count(),
            'published' => Post::where('status', PostStatus::PUBLISHED)->count(),
            'failed' => Post::where('status', PostStatus::FAILED)
                ->orWhereHas('variants', fn($v) => $v->where('status', PostStatus::FAILED))
                ->count(),
            'pending_approval' => Post::where('status', PostStatus::PENDING_APPROVAL)->count(),
            'draft' => Post::where('status', PostStatus::DRAFT)->count(),
        ];

        $selectedPost = null;
        if ($this->selectedPostId) {
            $selectedPost = Post::with(['client', 'author', 'variants.socialProfile', 'variants.mediaAssets', 'variants.publishingRecord.attempts'])
                ->find($this->selectedPostId);
        }

        return view('livewire.posts.post-list', [
            'posts' => $posts,
            'clients' => $clients,
            'platforms' => SocialPlatform::cases(),
            'metrics' => $metrics,
            'selectedPost' => $selectedPost,
        ]);
    }
}

