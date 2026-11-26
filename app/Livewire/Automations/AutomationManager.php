<?php

namespace App\Livewire\Automations;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\ReportConfig;
use App\Models\SocialProfile;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AutomationManager extends Component
{
    public array $automations = [];
    public string $newName = '';
    public string $newType = 'rss_feed';
    public string $newSourceUrl = '';
    public ?int $newTargetProfileId = null;
    public bool $showCreateModal = false;
    public ?string $feedbackMessage = null;

    public function mount(): void
    {
        $workspace = app('current_workspace');
        $this->automations = $workspace?->settings['automations'] ?? [];
    }

    public function createAutomation(): void
    {
        $this->validate([
            'newName' => 'required|string|min:2|max:80',
            'newType' => 'required|in:rss_feed,evergreen_queue,auto_analytics',
            'newSourceUrl' => 'nullable|url|max:255',
        ]);

        $workspace = app('current_workspace');
        if (!$workspace) {
            return;
        }

        $this->automations[] = [
            'id' => (string) Str::uuid(),
            'name' => $this->newName,
            'type' => $this->newType,
            'source_url' => $this->newSourceUrl,
            'target_profile_id' => $this->newTargetProfileId,
            'is_active' => true,
            'created_at' => now()->toDateTimeString(),
        ];

        $settings = $workspace->settings ?? [];
        $settings['automations'] = $this->automations;
        $workspace->update(['settings' => $settings]);

        $this->reset(['newName', 'newSourceUrl', 'newTargetProfileId', 'showCreateModal']);
        $this->feedbackMessage = "Automation workflow created and enabled!";
    }

    public function toggleAutomation(string $id): void
    {
        foreach ($this->automations as &$rule) {
            if (($rule['id'] ?? '') === $id) {
                $rule['is_active'] = !($rule['is_active'] ?? true);
            }
        }
        $this->saveAutomations();
    }

    public function deleteAutomation(string $id): void
    {
        $this->automations = array_values(array_filter(
            $this->automations,
            fn ($r) => ($r['id'] ?? '') !== $id
        ));
        $this->saveAutomations();
        $this->feedbackMessage = "Automation removed.";
    }

    protected function saveAutomations(): void
    {
        $workspace = app('current_workspace');
        if ($workspace) {
            $settings = $workspace->settings ?? [];
            $settings['automations'] = $this->automations;
            $workspace->update(['settings' => $settings]);
        }
    }

    public function render()
    {
        $profiles = SocialProfile::where('is_active', true)->get();
        $scheduledCount = Post::where('status', PostStatus::SCHEDULED)->count();
        $publishedCount = Post::where('status', PostStatus::PUBLISHED)->count();
        $reportSchedulesCount = ReportConfig::where('is_active', true)->count();

        // Last 7 days automation/publishing activity for Chart.js
        $chartLabels = [];
        $chartPublished = [];
        $chartScheduled = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartLabels[] = $day->format('M d');
            $chartPublished[] = Post::where('status', PostStatus::PUBLISHED)->whereDate('created_at', $day->toDateString())->count();
            $chartScheduled[] = Post::where('status', PostStatus::SCHEDULED)->whereDate('scheduled_at', $day->toDateString())->count();
        }

        return view('livewire.automations.automation-manager', [
            'profiles' => $profiles,
            'stats' => [
                'scheduled' => $scheduledCount,
                'published' => $publishedCount,
                'report_schedules' => $reportSchedulesCount,
                'active_rules' => collect($this->automations)->where('is_active', true)->count(),
            ],
            'chartData' => [
                'labels' => $chartLabels,
                'published' => $chartPublished,
                'scheduled' => $chartScheduled,
            ],
        ]);
    }
}

