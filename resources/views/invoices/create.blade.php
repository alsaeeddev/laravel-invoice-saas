@extends('layouts.app')
@section('title', 'Create Invoice')

@push('styles')
<style>
.line-item-row { background: #f8fafc; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; position: relative; }
.line-item-row:hover { background: #f1f5f9; }
.remove-btn { position: absolute; top: 0.5rem; right: 0.5rem; }
.totals-box { background: #f8fafc; border-radius: 10px; padding: 1rem 1.25rem; }
.total-row { display: flex; justify-content: space-between; font-size: 0.875rem; padding: 0.2rem 0; }
.total-row.grand-total { font-size: 1.1rem; font-weight: 700; border-top: 2px solid #e2e8f0; padding-top: 0.5rem; margin-top: 0.25rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Create Invoice</h1>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<form method="POST" action="{{ route('invoices.store') }}" x-data="invoiceForm()">
    @csrf
    <div class="row g-3">
        <!-- Left: Main form -->
        <div class="col-lg-8">
            <!-- Header info -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer *</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}{{ $customer->company ? ' ('.$customer->company.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date *</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-700">Line Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>

                    <!-- Product picker (hidden, used to prefill) -->
                    <div class="mb-3">
                        <select id="productPicker" class="form-select form-select-sm" @change="pickProduct($event)">
                            <option value="">Quick add from products...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    data-tax="{{ $product->tax_rate ?? $business->default_tax_rate }}">
                                    {{ $product->name }} — ${{ number_format($product->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Items list -->
                    <template x-for="(item, index) in items" :key="index">
                        <div class="line-item-row">
                            <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label mb-1" style="font-size:0.75rem;">Description *</label>
                                    <input type="text" :name="'items['+index+'][description]'" x-model="item.description"
                                        class="form-control form-control-sm" placeholder="Item description" required>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label mb-1" style="font-size:0.75rem;">Qty</label>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity"
                                        class="form-control form-control-sm" min="0.01" step="0.01" @input="recalc()" required>
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label mb-1" style="font-size:0.75rem;">Unit Price</label>
                                    <input type="number" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price"
                                        class="form-control form-control-sm" min="0" step="0.01" @input="recalc()" required>
                                </div>
                                <div class="col-sm-2">
                                    <label class="form-label mb-1" style="font-size:0.75rem;">Tax %</label>
                                    <input type="number" :name="'items['+index+'][tax_rate]'" x-model.number="item.tax_rate"
                                        class="form-control form-control-sm" min="0" max="100" step="0.01" @input="recalc()">
                                </div>
                                <div class="col-sm-2">
                                    <label class="form-label mb-1" style="font-size:0.75rem;">Discount $</label>
                                    <input type="number" :name="'items['+index+'][discount_amount]'" x-model.number="item.discount_amount"
                                        class="form-control form-control-sm" min="0" step="0.01" @input="recalc()">
                                </div>
                                <div class="col-sm-2 d-flex align-items-end">
                                    <div class="text-end w-100">
                                        <div style="font-size:0.7rem;color:#64748b;">Line Total</div>
                                        <strong style="font-size:0.9rem;" x-text="'$'+lineTotal(item).toFixed(2)"></strong>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-btn" @click="removeItem(index)" x-show="items.length > 1">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Notes & Terms -->
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Thank you for your business...">{{ old('notes', $business->invoice_notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Terms</label>
                            <textarea name="terms" class="form-control" rows="3" placeholder="Payment due within 30 days...">{{ old('terms', $business->invoice_terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Totals + Actions -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-700 mb-3">Summary</h6>
                    <div class="totals-box">
                        <div class="total-row"><span class="text-muted">Subtotal</span><span x-text="'$'+subtotal.toFixed(2)">$0.00</span></div>
                        <div class="total-row"><span class="text-muted">Discount</span><span class="text-success" x-text="'-$'+discountTotal.toFixed(2)">-$0.00</span></div>
                        <div class="total-row"><span class="text-muted">Tax</span><span x-text="'$'+taxTotal.toFixed(2)">$0.00</span></div>
                        <div class="total-row grand-total"><span>Total</span><span x-text="'$'+total.toFixed(2)">$0.00</span></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        items: [{ product_id: null, description: '', quantity: 1, unit_price: 0, tax_rate: {{ auth()->user()->currentBusiness()->default_tax_rate ?? 0 }}, discount_amount: 0 }],
        subtotal: 0, taxTotal: 0, discountTotal: 0, total: 0,

        addItem() {
            this.items.push({ product_id: null, description: '', quantity: 1, unit_price: 0, tax_rate: {{ auth()->user()->currentBusiness()->default_tax_rate ?? 0 }}, discount_amount: 0 });
        },
        removeItem(index) {
            this.items.splice(index, 1);
            this.recalc();
        },
        pickProduct(event) {
            const opt = event.target.selectedOptions[0];
            if (!opt.value) return;
            this.items.push({
                product_id: opt.value,
                description: opt.dataset.name,
                quantity: 1,
                unit_price: parseFloat(opt.dataset.price),
                tax_rate: parseFloat(opt.dataset.tax || 0),
                discount_amount: 0
            });
            event.target.value = '';
            this.recalc();
        },
        lineTotal(item) {
            const gross = item.quantity * item.unit_price;
            const disc  = Math.min(item.discount_amount || 0, gross);
            const taxable = gross - disc;
            const tax = taxable * ((item.tax_rate || 0) / 100);
            return Math.round((taxable + tax) * 100) / 100;
        },
        recalc() {
            let sub = 0, tax = 0, disc = 0;
            this.items.forEach(item => {
                const gross = item.quantity * item.unit_price;
                const d = Math.min(item.discount_amount || 0, gross);
                const taxable = gross - d;
                sub  += taxable;
                tax  += taxable * ((item.tax_rate || 0) / 100);
                disc += d;
            });
            this.subtotal      = Math.round(sub * 100) / 100;
            this.taxTotal      = Math.round(tax * 100) / 100;
            this.discountTotal = Math.round(disc * 100) / 100;
            this.total         = Math.round((sub + tax) * 100) / 100;
        }
    }
}
</script>
@endpush
