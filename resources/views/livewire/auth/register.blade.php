<div>
    <h4 class="fw-bold text-dark mb-1">Create Your Account</h4>
    <p class="text-muted small mb-4">Set up your agency workspace and start your 14-day trial.</p>

    <form wire:submit="register">
        <!-- Full Name -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Your Full Name</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="John Doe" required autofocus>
            </div>
            @error('name') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Work Email -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Work Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="john@agency.com" required>
            </div>
            @error('email') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Agency / Workspace Name -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Agency or Company Name</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-briefcase"></i></span>
                <input type="text" class="form-control @error('workspace_name') is-invalid @enderror" wire:model="workspace_name" placeholder="Apex Digital Media" required>
            </div>
            @error('workspace_name') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
            <div class="form-text small">This creates your isolated multi-tenant workspace.</div>
        </div>

        <!-- Timezone -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Primary Timezone</label>
            <select class="form-select @error('timezone') is-invalid @enderror" wire:model="timezone">
                <option value="UTC">UTC (Universal Coordinated Time)</option>
                <option value="Asia/Kolkata">Asia/Kolkata (IST +5:30)</option>
                <option value="America/New_York">America/New_York (EST)</option>
                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                <option value="Europe/London">Europe/London (GMT)</option>
                <option value="Europe/Paris">Europe/Paris (CET)</option>
                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
            </select>
            @error('timezone') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="Minimum 8 characters" required>
            </div>
            @error('password') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label small fw-semibold">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                <input type="password" class="form-control" wire:model="password_confirmation" placeholder="Repeat password" required>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
            <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span wire:loading.remove><i class="bi bi-arrow-right-circle-fill me-1"></i></span>
            <span>Create Workspace & Start Trial</span>
        </button>
    </form>

    <div class="text-center mt-4 pt-2 border-top">
        <span class="text-muted small">Already have an account?</span>
        <a href="{{ route('login') }}" class="small fw-semibold text-primary text-decoration-none ms-1">Sign In</a>
    </div>
</div>

