<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function create()
    {
        return view('business.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $business = Business::create($validated);
            auth()->user()->businesses()->attach($business->id, [
                'role'   => 'owner',
                'status' => 'active',
            ]);
            session(['current_business_id' => $business->id]);
        });

        return redirect()->route('dashboard')->with('success', 'Business created successfully!');
    }
}
