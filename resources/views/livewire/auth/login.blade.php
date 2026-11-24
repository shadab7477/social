<div>
    <h4 class="fw-bold text-dark mb-1">Sign In to PostSync</h4>
    <p class="text-muted small mb-4">Enter your credentials to access your agency workspace.</p>

    <form wire:submit="login">
        <!-- Email -->
        <div class="mb-3">
            <label class="form-label small fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="john@agency.com" required autofocus>
            </div>
            @error('email') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-semibold mb-0">Password</label>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="••••••••" required>
            </div>
            @error('password') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="rememberMe" wire:model="remember" checked>
            <label class="form-check-label small text-muted" for="rememberMe">
                Remember me on this device (Stay signed in)
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
            <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span wire:loading.remove><i class="bi bi-box-arrow-in-right me-1"></i></span>
            <span>Sign In</span>
        </button>
    </form>

    <div class="text-center mt-4 pt-2 border-top">
        <span class="text-muted small">Don't have a workspace yet?</span>
        <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">Start Free Trial</a>
    </div>
</div>

