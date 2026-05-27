<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\PurchaseReturn;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SupplierController extends Controller
{
   public function index(Request $request)
    {
        $query = Supplier::query();

        // 🔍 Search
        if ($request->search) {
            $query->where('supplier_name', 'like', '%'.$request->search.'%');
        }

        $suppliers = $query
            ->orderBy('id','desc')
            ->paginate(10)
            ->withQueryString();

        return view('supplier.index', compact('suppliers'));
    }

    public function SupplierForm()
    {
        return view('supplier.creat');
    }

    public function create(Request $request)
    {
        $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'mobile'         => 'required|digits:11',
            'BankName'       => 'nullable|string|max:255',
            'AccountNumber'  => 'nullable|digits:11',
            'address'        => 'required|string|max:500',
        ]);

        $supplier = new Supplier();
        $supplier->supplier_name  = $request->supplier_name;
        $supplier->mobile         = $request->mobile;
        $supplier->BankName       = $request->BankName;          
        $supplier->AccountNumber  = $request->AccountNumber; 
        $supplier->address        = $request->address;
        $supplier->save();

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Supplier added successfully!');
    }
    public function Edit($id)
    {
        $suppliers = Supplier::findorfail($id);
        return View('supplier.edit', compact('suppliers'));
    }

    public function update(Request $request, $id)
    {
        $suppliers = Supplier::findorfail($id);
        $suppliers->supplier_name=$request->supplier_name;
        $suppliers->BankName = $request->BankName;          
        $suppliers->AccountNumber = $request->AccountNumber; 
        $suppliers->mobile=$request->mobile;
        $suppliers->address=$request->address;
        $suppliers->save();
        $suppliers = Supplier::all();
        return View('supplier.index', compact('suppliers'));
    }

    public function Destry($id)
    {
        $suppliers = Supplier::findOrFail($id);
        $suppliers->delete();
        return redirect()->back()->with('success', 'Supplier deleted successfully!');
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term');
        $suppliers = Supplier::where('supplier_name', 'LIKE', "%{$term}%")
            ->orWhere('mobile', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get();

        $result = [];
        foreach ($suppliers as $supplier) {
            $result[] = [
                'id'    => $supplier->id,
                'value' => $supplier->supplier_name,
                'mobile'=> $supplier->mobile,
            ];
        }

        return response()->json($result);
    }

    public function info($id)
    {
        $supplier = Supplier::findOrFail($id);
        $dues = Purchase::where('supplier_id',$id)->where('due_amount','>',0)->get();

        return response()->json([
            'name' => $supplier->supplier_name,
            'mobile' => $supplier->mobile,
            'address' => $supplier->address,
            'total_due' => $dues->sum('due_amount'),
            'dues' => $dues->map(function($p){
                return [
                    'Invoice_no' => $p->Invoice_no,
                    'due_amount' => $p->due_amount,
                ];
            }),
        ]);
    }

    public function ledgerPdf(Request $request, $supplierId)
    {
        $from = $request->from;
        $to   = $request->to;
        $supplier = Supplier::findOrFail($supplierId);
        $ledger = collect();
        // Opening Balance (Before From Date)
        $openingBalance = 0;

        if ($from) {

            $prevDate = Carbon::parse($from)->subDay();

            // Previous Purchases (Credit)
            $prevPurchase = Purchase::where('supplier_id', $supplierId)
                ->where('date', '<=', $prevDate)
                ->sum('subtotal');

            // Previous Returns (Debit)
            $prevReturn = PurchaseReturn::where('supplier_id', $supplierId)
                ->where('return_date', '<=', $prevDate)
                ->sum('total_amount');

            // Previous Payments (Debit)
            $prevPayment = SupplierPayment::where('supplier_id', $supplierId)
                ->where('payment_date', '<=', $prevDate)
                ->sum('amount');

            // Debit - Credit
            $openingBalance = ($prevReturn + $prevPayment) - $prevPurchase;
        }

        // Purchases (CREDIT)
        $purchases = Purchase::with('items.product')
            ->where('supplier_id', $supplierId)
            ->when($from, fn($q)=>$q->where('date','>=',$from))
            ->when($to, fn($q)=>$q->where('date','<=',$to))
            ->get();

        foreach ($purchases as $p) {

            $items = $p->items->map(function($i){
                return [
                    'name' => $i->product->product_name ?? '',
                    'qty'  => $i->qty,
                    'rate' => $i->price
                ];
            });

            $ledger->push([
                'date'    => $p->date,
                'type'    => 'Purchase',
                'voucher' => $p->Invoice_no,
                'debit'   => 0,
                'credit'  => $p->subtotal,
                'items'   => $items,
                'note'    => $p->note ?? '',
            ]);
        }
        // Purchase Return (DEBIT)

        $returns = PurchaseReturn::with('items.product')
            ->where('supplier_id', $supplierId)
            ->when($from, fn($q)=>$q->where('return_date','>=',$from))
            ->when($to, fn($q)=>$q->where('return_date','<=',$to))
            ->get();

        foreach ($returns as $r) {
            $items = $r->items->map(function($i){
                return [
                    'name' => $i->product->product_name ?? '',
                    'qty'  => $i->qty,
                    'rate' => $i->price
                ];
            });
            $ledger->push([
                'date'    => $r->return_date,
                'type'    => 'Purchase Return',
                'voucher' => $r->return_no,
                'debit'   => $r->total_amount,
                'credit'  => 0,
                'items'   => $items,
                'note'    => $r->note ?? '',
            ]);
        }

        // Payments (DEBIT)
        $payments = SupplierPayment::where('supplier_id', $supplierId)
            ->when($from, fn($q)=>$q->where('payment_date','>=',$from))
            ->when($to, fn($q)=>$q->where('payment_date','<=',$to))
            ->get();
        foreach ($payments as $p) {
            $ledger->push([
                'date'    => $p->payment_date,
                'type'    => 'Payment',
                'voucher' => $p->voucher_no ?? '',
                'debit'   => $p->amount,
                'credit'  => 0,
                'items'   => collect(),
                'note'    => $p->note ?? '',
            ]);
        }


        // Sort by Date
        $ledger = $ledger->sortBy('date')->values();

        // Running Balance
        $runningBalance = $openingBalance;
        $totalDebit  = 0;
        $totalCredit = 0;
        $ledger = $ledger->map(function ($row) use (&$runningBalance, &$totalDebit, &$totalCredit){
            $debit  = $row['debit'];
            $credit = $row['credit'];
            $totalDebit  += $debit;
            $totalCredit += $credit;
            $runningBalance += ($debit - $credit);
            $row['running_balance'] = $runningBalance;
            return $row;
        });

        $closingBalance = $runningBalance;
        // Generate PDF

        $pdf = Pdf::loadView('supplier.ledger_pdf', [
            'supplier'       => $supplier,
            'ledger'         => $ledger,
            'from'           => $from,
            'to'             => $to,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,

        ]);

        $currentDate = Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = $supplier->id . '_' .$supplier->supplier_name. '_' . $currentDate . 'supplier_ledger.pdf';
        return $pdf->download($fileName);
    }
    public function paymentList($id)
    {
        $supplier = Supplier::findOrFail($id);
        $payments = SupplierPayment::where('supplier_id', $id)
                        ->orderBy('payment_date', 'asc')
                        ->get();
        $purchases = Purchase::with('supplier')
                        ->where('supplier_id', $id)
                        ->orderBy('date', 'asc')
                        ->get();

        return view('supplier.payment_list', compact('supplier', 'payments', 'purchases'));
    }
}
