<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\Villa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with('villa');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('villa_id')) {
            $query->where('villa_id', $request->input('villa_id'));
        }

        if ($request->filled('month')) {
            $query->whereMonth('expense_date', (int) $request->input('month'));
        }

        if ($request->filled('year')) {
            $query->whereYear('expense_date', (int) $request->input('year'));
        }

        $totalExpenses = (float) (clone $query)->sum('amount');
        $expenses = $query->latest('expense_date')->paginate(12)->withQueryString();

        $villas = Villa::orderBy('code')->get();
        $categories = [
            Expense::CATEGORY_UTILITIES => 'Utilities',
            Expense::CATEGORY_SUPPLIES => 'Supplies & Linens',
            Expense::CATEGORY_MAINTENANCE => 'Repairs & Maintenance',
            Expense::CATEGORY_STAFF_PAYROLL => 'Staff & Contractors',
            Expense::CATEGORY_MARKETING => 'Marketing & Listing',
            Expense::CATEGORY_FOOD_BEVERAGE => 'Food & Beverage',
            Expense::CATEGORY_OTHER => 'General Resort Overhead',
        ];

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'villas' => $villas,
            'categories' => $categories,
            'filters' => $request->only(['category', 'villa_id', 'month', 'year']),
        ]);
    }

    public function create(): View
    {
        $villas = Villa::orderBy('code')->get();

        return view('expenses.create', [
            'villas' => $villas,
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $expense = Expense::create($request->validated());

        return redirect()->route('expenses.index')
            ->with('success', "Expense '{$expense->title}' recorded.");
    }

    public function edit(Expense $expense): View
    {
        $villas = Villa::orderBy('code')->get();

        return view('expenses.edit', [
            'expense' => $expense,
            'villas' => $villas,
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')
            ->with('success', "Expense '{$expense->title}' updated.");
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense record removed.');
    }
}
