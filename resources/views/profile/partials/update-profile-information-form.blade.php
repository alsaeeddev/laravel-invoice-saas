<section>
    <header class="mb-4">
        <h5 class="fw-bold mb-1" style="color:#0f172a;">
            <i class="bi bi-person-circle me-2 text-primary"></i>{{ __('Profile Information') }}
        </h5>
        <p class="mb-0" style="color:#64748b;font-size:0.875rem;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ __('Profile saved successfully.') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold small">{{ __('Name') }}</label>
            <input id="name" name="name" type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">{{ __('Email') }}</label>
            <input id="email" name="email" type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning py-2 mt-3 mb-0 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success py-2 mt-2 mb-0 small">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif
            @endif
        </div>

        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check2 me-1"></i>{{ __('Save Changes') }}
        </button>
    </form>
</section>