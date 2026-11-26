<?php

namespace App\Livewire\Billing;

use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\SocialProfile;
use App\Models\UsageRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BillingManager extends Component
{
    public string $selectedPlan = 'agency_pro';
    public string $billingCycle = 'monthly';
    public ?string $feedbackMessage = null;

    public function mount(): void
    {
        $workspace = app('current_workspace');
        $settings = $workspace?->settings ?? [];
        $this->selectedPlan = $settings['billing_plan'] ?? 'agency_pro';
        $this->billingCycle = $settings['billing_cycle'] ?? 'monthly';
    }

    public function selectPlan(string $plan): void
    {
        $workspace = app('current_workspace');
        if (!$workspace) {
            return;
        }

        $settings = $workspace->settings ?? [];
        $settings['billing_plan'] = $plan;
        $settings['billing_updated_at'] = now()->toIso8601String();
        $workspace->update(['settings' => $settings]);

        $this->selectedPlan = $plan;
        $this->feedbackMessage = "Workspace plan updated to " . ucwords(str_replace('_', ' ', $plan)) . "!";
    }

    public function toggleCycle(string $cycle): void
    {
        $this->billingCycle = $cycle;
        $workspace = app('current_workspace');
        if ($workspace) {
            $settings = $workspace->settings ?? [];
            $settings['billing_cycle'] = $cycle;
            $workspace->update(['settings' => $settings]);
        }
    }

    public function render()
    {
        $workspace = app('current_workspace');
        $month = now()->format('Y-m');

        $profilesCount = SocialProfile::count();
        $membersCount = $workspace ? $workspace->members()->count() : 1;
        $postsThisMonth = Post::where('created_at', '>=', now()->startOfMonth())->count();
        $aiRequests = (int) UsageRecord::where('metric_key', 'ai_requests_count')
            ->where('billing_cycle_month', $month)
            ->value('usage_count');
        $storageMb = round((MediaAsset::sum('file_size') ?? 0) / (1024 * 1024), 2);

        $plans = [
            'starter' => [
                'name' => 'Starter',
                'monthly' => 29,
                'annual' => 24,
                'profiles' => 10,
                'members' => 3,
                'ai_limit' => 250,
                'storage_mb' => 2048,
                'badge' => 'For Solo Creators',
            ],
            'agency_pro' => [
                'name' => 'Agency Pro',
                'monthly' => 79,
                'annual' => 65,
                'profiles' => 50,
                'members' => 15,
                'ai_limit' => 2000,
                'storage_mb' => 10240,
                'badge' => 'Most Popular',
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'monthly' => 199,
                'annual' => 165,
                'profiles' => 250,
                'members' => 100,
                'ai_limit' => 10000,
                'storage_mb' => 51200,
                'badge' => 'Unlimited Scale',
            ],
        ];

        $activePlan = $plans[$this->selectedPlan] ?? $plans['agency_pro'];

        return view('livewire.billing.billing-manager', [
            'workspace' => $workspace,
            'plans' => $plans,
            'activePlan' => $activePlan,
            'usage' => [
                'profiles' => $profilesCount,
                'members' => $membersCount,
                'posts' => $postsThisMonth,
                'ai_requests' => $aiRequests,
                'storage_mb' => $storageMb,
            ],
        ]);
    }
}

