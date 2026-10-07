<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StaffController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness();
        $this->ensureOwner($business);
        $staff = $business->users()->orderBy('name')->get();
        return view('staff.index', compact('business', 'staff'));
    }

    public function invite(Request $request)
    {
        $business = auth()->user()->currentBusiness();
        $this->ensureOwner($business);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($business->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member.']);
        }

        $business->users()->attach($user->id, [
            'role'       => 'staff',
            'status'     => 'active',
            'invited_at' => now(),
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff member added.');
    }

    public function remove(Request $request, User $user)
    {
        $business = auth()->user()->currentBusiness();
        $this->ensureOwner($business);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot remove yourself.']);
        }

        $business->users()->detach($user->id);

        return redirect()->route('staff.index')->with('success', 'Staff member removed.');
    }

    private function ensureOwner(Business $business): void
    {
        if (! auth()->user()->isOwnerOf($business)) {
            abort(403, 'Only the business owner can manage staff.');
        }
    }
}
