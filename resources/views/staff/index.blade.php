@extends('layouts.app')
@section('title', 'Team')
@section('content')
<div class="page-header">
    <h1>Team Members</h1>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($staff as $member)
                            <tr>
                                <td class="fw-600">{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td><span class="badge bg-{{ $member->pivot->role === 'owner' ? 'primary' : 'secondary' }}">{{ ucfirst($member->pivot->role) }}</span></td>
                                <td><span class="badge bg-{{ $member->pivot->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($member->pivot->status) }}</span></td>
                                <td>
                                    @if($member->id !== auth()->id() && $member->pivot->role !== 'owner')
                                        <form method="POST" action="{{ route('staff.remove', $member) }}" onsubmit="return confirm('Remove this member?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-x"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-700 mb-3">Add Team Member</h6>
                <form method="POST" action="{{ route('staff.invite') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">User Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="colleague@example.com" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">The user must already have an account.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Staff Member</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
