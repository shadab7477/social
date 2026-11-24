<?php

use App\Http\Controllers\WorkspaceSwitchController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Route;

// Public Landing Page & Marketing
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/pricing', function () {
    return view('welcome');
})->name('pricing');

// Legal & Meta Compliance Pages
Route::get('/privacy-policy', function () {
    return view('legal.privacy');
})->name('legal.privacy');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('legal.terms');

Route::get('/data-deletion', function () {
    return view('legal.deletion');
})->name('legal.deletion');

Route::get('/deletion-status', function () {
    return view('legal.deletion-status');
})->name('legal.deletion.status');

// Webhooks Endpoints (CSRF Exempt)
Route::get('/webhooks/meta', [\App\Http\Controllers\MetaWebhookController::class, 'verify'])->name('webhooks.meta.verify');
Route::post('/webhooks/meta', [\App\Http\Controllers\MetaWebhookController::class, 'handle'])->name('webhooks.meta.handle');
Route::post('/webhooks/meta/data-deletion', [\App\Http\Controllers\MetaWebhookController::class, 'dataDeletion'])->name('webhooks.meta.data-deletion');

// Guest Authentication Routes (Livewire 3)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Workspace Team Invitation Acceptance (Public or Auth)
Route::get('/invite/accept/{token}', \App\Http\Controllers\WorkspaceInviteController::class)->name('invitations.accept');

// Authenticated User Session Termination
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

// Protected Multi-Tenant SaaS Workspace Routes
Route::middleware(['auth', 'workspace'])->group(function () {
    // Workspace Switching & Creation
    Route::post('/workspace/switch/{workspaceId}', WorkspaceSwitchController::class)->name('workspace.switch');
    Route::post('/workspaces/create', \App\Http\Controllers\WorkspaceCreateController::class)->name('workspaces.create');

    // Dashboard & Overview
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Publishing & Content
    Route::get('/composer', \App\Livewire\Composer\PostComposer::class)->name('composer');
    Route::get('/calendar', \App\Livewire\Calendar\ContentCalendar::class)->name('calendar');
    Route::get('/posts', \App\Livewire\Posts\PostList::class)->name('posts.index');

    Route::get('/media', \App\Livewire\MediaLibrary\MediaManager::class)->name('media.index');

    // Engagement & Social Profiles
    Route::get('/social-accounts', \App\Livewire\SocialAccounts\AccountManager::class)->name('social-accounts.index');

    // OAuth 2.0 PKCE Handshake Endpoints
    Route::get('/oauth/connect/{platform}', [\App\Http\Controllers\SocialOAuthController::class, 'connect'])->name('oauth.connect');
    Route::get('/oauth/callback/{platform}', [\App\Http\Controllers\SocialOAuthController::class, 'callback'])->name('oauth.callback');

    Route::get('/inbox', \App\Livewire\Inbox\UnifiedInbox::class)->name('inbox.index');

    Route::get('/analytics', \App\Livewire\Analytics\AnalyticsDashboard::class)->name('analytics.index');

    Route::get('/reports', \App\Livewire\Reports\ReportBuilder::class)->name('reports.index');
    Route::get('/reports/{report}/download', [\App\Http\Controllers\ReportDownloadController::class, 'download'])->name('reports.download');

    // Clients & Approvals
    Route::get('/clients', \App\Livewire\Clients\ClientManager::class)->name('clients.index');
    Route::get('/approvals', \App\Livewire\Approvals\ApprovalCenter::class)->name('approvals.index');

    Route::get('/team', \App\Livewire\Team\MemberManager::class)->name('team.index');

    Route::get('/automations', function () {
        return view('dashboard', ['headerTitle' => 'Automations & RSS']);
    })->name('automations.index');

    // Billing & Workspace Settings
    Route::get('/billing', function () {
        return view('dashboard', ['headerTitle' => 'Billing & Subscriptions']);
    })->name('billing.index');

    Route::get('/settings', function () {
        return view('dashboard', ['headerTitle' => 'Workspace Settings']);
    })->name('settings.index');
});

// Super Administrator Platform Master Control Panel
Route::middleware(['auth', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/', \App\Livewire\Admin\AdminDashboard::class)->name('admin.dashboard');
});

// Background Cron Runner Trigger (for shared hosting or external cron services)
Route::get('/cron/run', function () {
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return response()->json([
        'status' => 'success',
        'message' => 'Scheduler executed successfully',
        'output' => trim(\Illuminate\Support\Facades\Artisan::output()),
    ]);
})->name('cron.run');

// Storage Asset Serving Fallback (ensures uploaded media works even without symlinks or on shared hosting)
Route::get('/storage/{path}', function (string $path) {
    $cleanPath = str_replace(['../', '..\\'], '', $path);
    $fullPath = storage_path('app/public/' . ltrim($cleanPath, '/\\'));

    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeType = match ($extension) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'svg' => 'image/svg+xml',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        'pdf' => 'application/pdf',
        default => mime_content_type($fullPath) ?: 'application/octet-stream',
    };

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('storage.local');

