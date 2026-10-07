<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $business = auth()->user()->currentBusiness();
        $this->authorizeOwner($business);
        return view('settings.index', compact('business'));
    }

    public function update(Request $request)
    {
        $business = auth()->user()->currentBusiness();
        $this->authorizeOwner($business);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address'        => ['nullable', 'string', 'max:500'],
            'city'           => ['nullable', 'string', 'max:100'],
            'country'        => ['nullable', 'string', 'max:100'],
            'website'        => ['nullable', 'url', 'max:255'],
            'tax_number'     => ['nullable', 'string', 'max:100'],
            'currency'       => ['required', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_notes'  => ['nullable', 'string', 'max:2000'],
            'invoice_terms'  => ['nullable', 'string', 'max:2000'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $filename = Str::random(40) . '.' . $request->file('logo')->getClientOriginalExtension();
            $validated['logo_path'] = $request->file('logo')->storeAs('logos', $filename, 'public');
        }

        unset($validated['logo']);
        $business->update($validated);

        return redirect()->route('settings.index')->with('success', 'Settings updated.');
    }

    private function authorizeOwner(Business $business): void
    {
        if (! auth()->user()->isOwnerOf($business)) {
            abort(403, 'Only the business owner can manage settings.');
        }
    }
    
    
        public function destroyLogo()
    {
        $business = auth()->user()->currentBusiness();
        $this->authorizeOwner($business);

        if ($business->logo_path) {
            Storage::disk('public')->delete($business->logo_path);
            $business->update(['logo_path' => null]);
        }

        return response()->json(['success' => true]);
    }

}
