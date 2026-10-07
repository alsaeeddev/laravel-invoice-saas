<section>
    <header class="mb-4">
        <h5 class="fw-bold mb-1" style="color:#0f172a;">
            <i class="bi bi-shield-lock me-2 text-primary"></i>{{ __('Update Password') }}
        </h5>
        <p class="mb-0" style="color:#64748b;font-size:0.875rem;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ __('Password updated successfully.') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold small">{{ __('Current Password') }}</label>
            <div class="input-group has-validation">
                <input id="update_password_current_password" name="current_password" type="password"
                       class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                       autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" data-toggle-password="update_password_current_password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                @if ($errors->updatePassword->has('current_password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                @endif
            </div>
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold small">{{ __('New Password') }}</label>
            <div class="input-group has-validation">
                <input id="update_password_password" name="password" type="password"
                       class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                       autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" data-toggle-password="update_password_password" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                @if ($errors->updatePassword->has('password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label fw-semibold small">{{ __('Confirm Password') }}</label>
            <div class="input-group has-validation">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                       class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                       autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" data-toggle-password="update_password_password_confirmation" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
                @if ($errors->updatePassword->has('password_confirmation'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-key me-1"></i>{{ __('Update Password') }}
        </button>
    </form>
</section>