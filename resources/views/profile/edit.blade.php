@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 820px;">

    {{-- Profile header --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold flex-shrink-0"
                 style="width:64px;height:64px;font-size:1.6rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h4 class="mb-0 fw-bold text-truncate" style="color:#0f172a;">{{ $user->name }}</h4>
                <div class="text-truncate" style="color:#64748b;font-size:0.9rem;">{{ $user->email }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="card border border-danger-subtle shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password show/hide toggle
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-toggle-password'));
            var icon = btn.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    // Delete modal auto-open on validation error
    @if ($errors->userDeletion->isNotEmpty())
        new bootstrap.Modal(document.getElementById('confirmUserDeletion')).show();
    @endif
});
</script>
@endsection