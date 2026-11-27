<div>
    <h4 class="fw-bold text-dark mb-1">Sign In to PostSync</h4>
    <p class="text-muted small mb-4">Passwordless email verification for Agency Team &amp; Client Portal.</p>

    @if (session('info'))
        <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-envelope-check me-1"></i> {{ session('info') }}
        </div>
    @endif

    @if (!$otpSent)
        <form wire:submit="sendOtp">
            <div class="mb-4">
                <label class="form-label small fw-semibold">Email / Gmail Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="you@gmail.com" required autofocus>
                </div>
                @error('email') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 py-2 rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="sendOtp" class="spinner-border spinner-border-sm"></span>
                <span>Send 6-Digit Login Code</span>
                <i wire:loading.remove wire:target="sendOtp" class="bi bi-arrow-right"></i>
            </button>
        </form>
    @else
        <form wire:submit="verifyOtp">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">6-Digit Code</label>
                    <button type="button" wire:click="changeEmail" class="btn btn-link btn-sm p-0 text-decoration-none">Change email</button>
                </div>
                <input type="text" maxlength="6" inputmode="numeric" class="form-control form-control-lg text-center fw-bold @error('otpCode') is-invalid @enderror" wire:model="otpCode" placeholder="000000" style="letter-spacing: 8px;" required autofocus>
                @error('otpCode') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
                <div class="form-text small">Check your inbox ({{ $email }}). Code expires in 10 minutes.</div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="rememberMe" wire:model="remember" checked>
                <label class="form-check-label small text-muted" for="rememberMe">Keep me signed in on this device</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2 mb-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="verifyOtp" class="spinner-border spinner-border-sm"></span>
                <i wire:loading.remove wire:target="verifyOtp" class="bi bi-shield-check"></i>
                <span>Verify &amp; Sign In</span>
            </button>

            <button type="button" wire:click="sendOtp" class="btn btn-light border w-100 py-2 rounded-pill small">
                Resend Code
            </button>
        </form>
    @endif

    <div class="text-center mt-4 pt-3 border-top">
        <span class="text-muted small">Don't have an agency workspace yet?</span>
        <a href="{{ route('register') }}" class="small fw-semibold text-primary text-decoration-none ms-1">Create Workspace</a>
    </div>
</div>
