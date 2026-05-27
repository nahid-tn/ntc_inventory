<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ExpenseItem;

class ExpenseController extends Controller
{
    public function index()
    {
        // Eager load items to prevent N+1 problem
        $expenses = Expense::with('items')
                ->orderBy('expense_date', 'desc')
                ->paginate(10); // 10 per page

        return view('expense.index', compact('expenses'));
    }
    // Show form for creating
    public function create()
    {
        return view('expense.create');
    }

    // Store new expense
    public function store(Request $request)
    {
        $request->validate([
            'expense_date'   => 'required|date',
            'categories.*'   => 'required|string',
            'qtys.*'         => 'required|integer|min:1',
            'prices.*'       => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            $qtys  = $request->input('qtys', []);
            $prices = $request->input('prices', []);

            foreach ($qtys as $i => $qty) {
                $price = $prices[$i] ?? 0;
                $total += $qty * $price;
            }

            $expense = Expense::create([
                'expense_date'   => $request->expense_date,
                'payment_method' => $request->payment_method,
                'total'          => $total,
            ]);

            foreach ($request->categories as $i => $category) {
                $qty   = $qtys[$i] ?? 1;
                $price = $prices[$i] ?? 0;
                $expense->items()->create([
                    'item_name' => $category,
                    'qty'       => $qty,
                    'price'     => $price,
                    'subtotal'  => $qty * $price,
                    'description' => $request->input('descriptions.' . $i, ''), // Optional description
                ]);
            }
        });

        return redirect()->route('expense.index')->with('success', 'Expense saved successfully!');
    }

    public function show($id)
    {
        $expense = Expense::with('items')->findOrFail($id);
        return view('expense.view', compact('expense'));
    }

    // Show edit form
    public function edit($id)
    {
        $expense = Expense::with('items')->findOrFail($id);
        return view('expense.edit', compact('expense'));
    }

    // Update expense
    public function update(Request $request, $id)
    {
        $request->validate([
            'expense_date'  => 'required|date',
            'categories'    => 'required|array',
            'categories.*'  => 'required|string',
            'qtys'          => 'required|array',
            'qtys.*'        => 'required|integer|min:1',
            'prices'        => 'required|array',
            'prices.*'      => 'required|numeric|min:0',
        ]);

        $expense = Expense::findOrFail($id);

        $categories = $request->categories;
        $qtys       = $request->qtys;
        $prices     = $request->prices;
        $descs      = $request->descriptions ?? [];

        $items = [];
        $total = 0;

        foreach ($categories as $i => $cat) {
            if (!empty($cat)) {

                $qty   = $qtys[$i] ?? 1;
                $price = $prices[$i] ?? 0;
                $sub   = $qty * $price;

                $items[] = [
                    'item_name'  => $cat,
                    'qty'        => $qty,
                    'price'      => $price,
                    'subtotal'   => $sub,
                    'description'=> $descs[$i] ?? '',
                ];

                $total += $sub;
            }
        }

        $expense->update([
            'expense_date'   => $request->expense_date,
            'payment_method' => $request->payment_method,
            'total'          => $total, // ✅ FINAL FIX
        ]);

        $expense->items()->delete();

        foreach ($items as $item) {
            $expense->items()->create($item);
        }

        return redirect()->route('expense.index')->with('success', 'Expense updated successfully.');
    }

    // Delete expense
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->items()->delete();
        $expense->delete();

        return redirect()->route('expense.index')->with('success', 'Expense deleted successfully.');
    }

    // Show report form
    public function reportForm()
    {
        return view('expense.reportform');
    }

    // Generate report PDF
    public function reportPdf(Request $request)
    {
        $request->validate([
            'type' => 'required|in:day,month,year',
            'date' => 'required',
        ]);

        $query = Expense::with('items');

        if ($request->type === 'day') {
            $query->whereDate('expense_date', $request->date);
        }
        if ($request->type === 'month') {
            $date = Carbon::parse($request->date);
            $query->whereMonth('expense_date', $date->month)
                  ->whereYear('expense_date', $date->year);
        }
        if ($request->type === 'year') {
            $query->whereYear('expense_date', $request->date);
        }

        $expenses = $query->orderBy('expense_date')->get();

        // Calculate total for all items
        $total = $expenses->sum(function($expense){
            return $expense->items->sum('subtotal');
        });

        $pdf = Pdf::loadView('expense.reportpdf', compact('expenses', 'total', 'request'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('expense_report.pdf');
    }

    // View report on page
    public function viewReport(Request $request)
    {
        $request->validate([
            'type' => 'required|in:day,month,year',
            'date' => 'required|date',
        ]);

        $type = $request->type;
        $date = Carbon::parse($request->date);

        $query = Expense::with('items');

        // Filter Logic
        if ($type === 'day') {
            $query->whereDate('expense_date', $date);
        } elseif ($type === 'month') {
            $query->whereMonth('expense_date', $date->month)
                  ->whereYear('expense_date', $date->year);
        } elseif ($type === 'year') {
            $query->whereYear('expense_date', $date->year);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        // Total Calculation (items থেকে)
        $total = $expenses->sum(function ($expense) {
            return $expense->items->sum('subtotal');
        });

        return view('expense.viewreport', compact('expenses', 'type', 'date', 'total'));
    }


}