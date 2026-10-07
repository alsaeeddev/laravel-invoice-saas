@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="page-header">
    <h1>Business Settings</h1>
</div>

<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PATCH')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3 border-bottom pb-2">Business Information</h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Business Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $business->name) }}" required>
                        </div>
{{-- Logo --}}
<div class="col-md-4">
    <label class="form-label">Logo</label>

    <div id="logo-preview-wrap" class="mb-2 {{ $business->logo_path ? '' : 'd-none' }}">
        <div class="position-relative d-inline-block">
            <img id="logo-preview"
                 @if($business->logo_path) src="{{ Storage::disk('public')->url($business->logo_path) }}" @endif
                 height="40" class="rounded border" alt="Business logo"
                 style="max-width:100%; object-fit:contain;">
            <button type="button" id="remove-logo-btn"
                    class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle p-0 d-flex align-items-center justify-content-center"
                    style="width:22px;height:22px;" title="Remove logo" aria-label="Remove logo">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>

    <input type="file" name="logo" id="logo-input"
           class="form-control form-control-sm @error('logo') is-invalid @enderror"
           accept=".png,.jpg,.jpeg">
    @error('logo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">PNG or JPG, max 2MB.</div>
    <div id="logo-msg" class="small mt-1"></div>
</div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $business->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $business->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website', $business->website) }}" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Number</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $business->tax_number) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $business->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $business->city) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $business->country) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3 border-bottom pb-2">Invoice Settings</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                @foreach(['USD','EUR','GBP','CAD','AUD','JPY','CHF','INR','PKR','AED'] as $c)
                                    <option value="{{ $c }}" {{ old('currency', $business->currency) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $business->invoice_prefix) }}" placeholder="INV-">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default Tax Rate (%)</label>
                            <input type="number" name="default_tax_rate" step="0.01" min="0" max="100" class="form-control" value="{{ old('default_tax_rate', $business->default_tax_rate) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Invoice Notes</label>
                            <textarea name="invoice_notes" class="form-control" rows="3">{{ old('invoice_notes', $business->invoice_notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Invoice Terms</label>
                            <textarea name="invoice_terms" class="form-control" rows="3">{{ old('invoice_terms', $business->invoice_terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input   = document.getElementById('logo-input');
    const preview = document.getElementById('logo-preview');
    const wrap    = document.getElementById('logo-preview-wrap');
    const btn     = document.getElementById('remove-logo-btn');
    const msg     = document.getElementById('logo-msg');

    let savedSrc   = preview.getAttribute('src') || '';
    let hasNewFile = false;

    const showMsg = (text, cls) => { msg.className = 'small mt-1 ' + cls; msg.textContent = text; };

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (!/^image\/(png|jpe?g)$/.test(file.type)) {
            showMsg('Sirf PNG ya JPG allowed hai.', 'text-danger');
            this.value = ''; return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showMsg('Logo 2MB se chota hona chahiye.', 'text-danger');
            this.value = ''; return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            wrap.classList.remove('d-none');
            hasNewFile = true;
            showMsg('', '');
        };
        reader.readAsDataURL(file);
    });

    btn.addEventListener('click', async function () {
        if (hasNewFile) {
            input.value = '';
            hasNewFile = false;
            if (savedSrc) { preview.src = savedSrc; }
            else { preview.removeAttribute('src'); wrap.classList.add('d-none'); }
            return;
        }

        if (!savedSrc) return;
        if (!confirm('Logo permanently delete karna hai?')) return;

        btn.disabled = true;
        try {
            const res = await fetch("{{ route('settings.logo.destroy') }}", {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error('Request failed');

            savedSrc = '';
            preview.removeAttribute('src');
            wrap.classList.add('d-none');
            input.value = '';
            showMsg('Logo removed.', 'text-success');
        } catch (err) {
            showMsg('Logo remove nahi ho saka, dobara try karo.', 'text-danger');
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
