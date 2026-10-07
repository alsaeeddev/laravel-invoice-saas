<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('category')->latest()->paginate(15);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            $file     = $request->file('receipt');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $data['receipt_path'] = $file->storeAs('receipts', $filename, 'local');
        }

        unset($data['receipt']);
        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'category_id'    => ['nullable', 'exists:expense_categories,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'receipt'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('local')->delete($expense->receipt_path);
            }
            $file     = $request->file('receipt');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $validated['receipt_path'] = $file->storeAs('receipts', $filename, 'local');
        }

        unset($validated['receipt']);
        $expense->update($validated);

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function downloadReceipt(Expense $expense)
    {
        if (! $expense->receipt_path || ! Storage::disk('local')->exists($expense->receipt_path)) {
            abort(404);
        }
        return Storage::disk('local')->download($expense->receipt_path);
    }
}
