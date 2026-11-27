<div>
    <h4 class="fw-bold text-dark mb-1">Create Your Account</h4>
    <p class="text-muted small mb-4">Sign up with your Gmail / work email using a 6-digit verification code.</p>

    @if (session('info'))
        <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
            <i class="bi bi-envelope-check me-1"></i> {{ session('info') }}
        </div>
    @endif

    @if (!$otpSent)
        <form wire:submit="sendOtp">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Your Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="John Doe" required autofocus>
                </div>
                @error('name') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Work Email / Gmail</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="you@gmail.com" required>
                </div>
                @error('email') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Agency or Company Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-briefcase"></i></span>
                    <input type="text" class="form-control @error('workspace_name') is-invalid @enderror" wire:model="workspace_name" placeholder="Apex Digital Media" required>
                </div>
                @error('workspace_name') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
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

            <button type="submit" class="btn btn-dark w-100 py-2 rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="sendOtp" class="spinner-border spinner-border-sm"></span>
                <span>Send Verification Code</span>
                <i wire:loading.remove wire:target="sendOtp" class="bi bi-arrow-right"></i>
            </button>
        </form>
    @else
        <form wire:submit="verifyAndRegister">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">Enter 6-Digit Email Code</label>
                    <button type="button" wire:click="editDetails" class="btn btn-link btn-sm p-0 text-decoration-none">Edit details</button>
                </div>
                <input type="text" maxlength="6" inputmode="numeric" class="form-control form-control-lg text-center fw-bold @error('otpCode') is-invalid @enderror" wire:model="otpCode" placeholder="000000" style="letter-spacing: clamp(4px, 2vw, 8px);" required autofocus>
                @error('otpCode') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
                <div class="form-text small">Verification code sent to {{ $email }}.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold d-flex align-items-center justify-content-center gap-2 mb-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="verifyAndRegister" class="spinner-border spinner-border-sm"></span>
                <i wire:loading.remove wire:target="verifyAndRegister" class="bi bi-check2-circle"></i>
                <span>Verify &amp; Create Workspace</span>
            </button>

            <button type="button" wire:click="sendOtp" class="btn btn-light border w-100 py-2 rounded-pill small">
                Resend Code
            </button>
        </form>
    @endif

    <div class="text-center mt-4 pt-3 border-top">
        <span class="text-muted small">Already have an account?</span>
        <a href="{{ route('login') }}" class="small fw-semibold text-primary text-decoration-none ms-1">Sign In</a>
    </div>
</div>
