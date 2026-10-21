<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PurchaseReturn;

class SupplierPaymentController extends Controller
{
   public function index(Request $request)
{
    $query = SupplierPayment::with([
        'supplier',
        'purchase.payments'
    ]);

    // 🔍 Supplier Name Search
    if ($request->search) {
        $query->whereHas('supplier', function ($q) use ($request) {
            $q->where('supplier_name', 'like', '%' . $request->search . '%');
        });
    }

    // 🔍 Supplier ID filter
    if ($request->supplier_id) {
        $query->where('supplier_id', $request->supplier_id);
    }

    // 📅 Date Filter
    if ($request->from_date) {
        $query->whereDate('payment_date', '>=', $request->from_date);
    }
    if ($request->to_date) {
        $query->whereDate('payment_date', '<=', $request->to_date);
    }

    // Pagination
    $payments = $query->orderBy('payment_date','desc')
        ->paginate(15)
        ->withQueryString();

    // 🧮 Due Calculation: Shipping বাদ + Partial payments
    $payments->getCollection()->transform(function ($payment) {

        if ($payment->purchase) {
            $totalWithoutShipping = ($payment->purchase->total_amount ?? 0) - ($payment->purchase->shipping_amount ?? 0);

            // Partial payments BEFORE or equal this payment date
            $paidSoFar = $payment->purchase->payments
                ->where('payment_date', '<=', $payment->payment_date)
                ->sum('amount');

            $payment->due_after = $totalWithoutShipping - $paidSoFar;

        } else {
            $payment->due_after = 0;
        }

        return $payment;
    });

    return view('supplier_payment.index', compact('payments'));
}

    public function create()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $purchases = Purchase::with('payments')
            ->orderBy('date','desc')
            ->get()
            ->filter(function ($purchase) {
                $paid = $purchase->payments->sum('amount');
                return $purchase->total_amount > $paid;
            });
        return view('supplier_payment.create', compact('suppliers','purchases'));
    }

    public function store(Request $request)
    {
        $rules = [
            'supplier_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required',
        ];

        if ($request->payment_method === 'Cheque') {
            $rules['cheque_number'] = 'required|string';
            $rules['cheque_image']  = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        if ($request->payment_method === 'Bank') {
            $rules['BankName']       = 'required|string';
            $rules['account_number'] = 'required|string';
        }

        if ($request->payment_method === 'Mobile Banking') {
            $rules['transaction_number'] = 'required|string';
            $rules['deposit_slip']       = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        $request->validate($rules);
        // ===================== Transaction Start =====================
        DB::transaction(function () use ($request) {
            $data = [
                'supplier_id'        => $request->supplier_id,
                'purchase_id'        => $request->purchase_id ?? null,
                'payment_date'       => $request->payment_date,
                'amount'             => $request->amount,
                'payment_method'     => $request->payment_method,
                'BankName'           => $request->BankName ?? null,
                'account_number'     => $request->account_number ?? null,
                'cheque_number'      => $request->cheque_number ?? null,
                'transaction_number' => $request->transaction_number ?? null,
                'note'               => $request->purchase_id ? 'Invoice Payment' : 'Advance Payment',
            ];

            if ($request->hasFile('cheque_image')) {
                $data['cheque_image'] = $request->file('cheque_image')->store('cheques', 'public');
            }

            if ($request->hasFile('deposit_slip')) {
                $data['deposit_slip'] = $request->file('deposit_slip')->store('deposits', 'public');
            }

            $payment = SupplierPayment::create($data);
            $amount = $request->amount;

            if ($request->purchase_id) {
                $purchase = Purchase::find($request->purchase_id);
                $paid = $purchase->payments()->sum('amount');
                $due = $purchase->total_amount - $paid;
                if ($amount > $due) {
                    $amount = $due;
                }
            } else {
                // Advance Payment: Apply to oldest unpaid invoices
                $dues = Purchase::where('supplier_id', $request->supplier_id)
                            ->orderBy('date')
                            ->get();
                foreach ($dues as $p) {
                    $paid = $p->payments()->sum('amount');
                    $due = $p->total_amount - $paid;
                    if ($due <= 0) continue;
                    if ($amount <= 0) break;
                    $apply = min($due, $amount);
                    $amount -= $apply;
                }
            }
        });

        return redirect()->route('supplier.payment.index')->with('success', 'Payment Saved Successfully');
    }

    public function ledger(Request $request, $id)
    {
        $supplier_id = $request->get('supplier_id', $id);
        $supplier = Supplier::findOrFail($supplier_id);
        $from = $request->get('from');
        $to   = $request->get('to');
        /* ================= Purchases ================= */
        $purchases = Purchase::where('supplier_id', $supplier_id)
            ->when($from, fn($q)=>$q->where('date','>=',$from))
            ->when($to, fn($q)=>$q->where('date','<=',$to))
            ->select('Invoice_no','date','subtotal','shipping_amount')
            ->get();
        $purchaseRows = collect();
        foreach($purchases as $p){
            // Main Purchase
            $purchaseRows->push([
                'date'   => $p->date,
                'note'   => 'Invoice: '.$p->Invoice_no,
                'debit'  => $p->subtotal,
                'credit' => 0,
                'type'   => 'purchase'
            ]);
            // Shipping Row (Info Only)
            if($p->shipping_amount > 0){
                $purchaseRows->push([
                    'date'   => $p->date,
                    'note'   => 'Shipping (Inv: '.$p->Invoice_no.')',
                    'debit'  => 0,
                    'credit' => 0,
                    'shipping' => $p->shipping_amount,
                    'type'   => 'shipping'
                ]);
            }
        }

        /* ================= Payments ================= */
        $payments = SupplierPayment::where('supplier_id', $supplier_id)
            ->when($from, fn($q)=>$q->where('payment_date','>=',$from))
            ->when($to, fn($q)=>$q->where('payment_date','<=',$to))
            ->select('payment_date','amount','note')
            ->get()
            ->map(fn($pay)=>[
                'date'   => $pay->payment_date,
                'note'   => $pay->note ?? 'Payment',
                'debit'  => 0,
                'credit' => $pay->amount,
                'type'   => 'payment'
            ]);
        /* ================= Returns ================= */
        $returns = PurchaseReturn::where('supplier_id',$supplier_id)
            ->when($from, fn($q)=>$q->where('return_date','>=',$from))
            ->when($to, fn($q)=>$q->where('return_date','<=',$to))
            ->select('return_no','return_date','total_amount')
            ->get()
            ->map(fn($r)=>[
                'date'   => $r->return_date,
                'note'   => 'Return: '.$r->return_no,
                'debit'  => 0,
                'credit' => $r->total_amount,
                'type'   => 'return'
            ]);
        /* ================= Merge ================= */
        $ledger = $purchaseRows
                    ->merge($payments)
                    ->merge($returns)
                    ->sortBy('date')
                    ->values();
        /* ================= Summary ================= */

        $totalPurchase = Purchase::where('supplier_id',$supplier_id)
                            ->sum('subtotal');
        $totalPaid = SupplierPayment::where('supplier_id',$supplier_id)
                            ->sum('amount');
        $totalReturn = PurchaseReturn::where('supplier_id',$supplier_id)
                            ->sum('total_amount');
        $totalPaidWithReturn = $totalPaid + $totalReturn;

        $balance = $totalPurchase - $totalPaid - $totalReturn;
        
        return view('supplier.ledger', compact(
            'supplier',
            'ledger',
            'totalPurchase',
            'totalPaid',
            'totalReturn',
            'totalPaidWithReturn',
            'balance',
            'from',
            'to'
        ));
    }


    // public function show($id)
    // {
    //     $payment = SupplierPayment::with('supplier','purchase')->findOrFail($id);
    //     return view('supplier_payment.show', compact('payment'));
    // }

    // public function show($id)
    // {
    //     $payment = SupplierPayment::with(['supplier', 'purchase'])->findOrFail($id);
    //     $supplier = $payment->supplier;
    //     $supplier_id = $supplier->id;

    //     // ====== Ledger এর মতো একই ফর্মুলা ======
    //     $totalPurchase = \App\Models\Purchase::where('supplier_id', $supplier_id)
    //                         ->sum('subtotal');

    //     $totalPaid = \App\Models\SupplierPayment::where('supplier_id', $supplier_id)
    //                         ->sum('amount');

    //     $totalReturn = \App\Models\PurchaseReturn::where('supplier_id', $supplier_id)
    //                         ->sum('total_amount');

    //     $totalPaidWithReturn = $totalPaid + $totalReturn;

    //     $balance = $totalPurchase - $totalPaid - $totalReturn;   // বর্তমান মোট বাকি

    //     // ====== এই Invoice এর আলাদা Due ======
    //     $invoiceDue = null;
    //     if ($payment->purchase) {
    //         $invoicePaid = \App\Models\SupplierPayment::where('purchase_id', $payment->purchase_id)
    //                             ->sum('amount');

    //         // যদি PurchaseReturn এ purchase_id থাকে
    //         $invoiceReturn = \App\Models\PurchaseReturn::where('purchase_id', $payment->purchase_id)
    //                             ->sum('total_amount');

    //         $invoiceDue = $payment->purchase->subtotal - $invoicePaid - $invoiceReturn;
    //     }

    //     return view('supplier_payment.show', compact(
    //         'payment',
    //         'totalPurchase',
    //         'totalPaid',
    //         'totalReturn',
    //         'totalPaidWithReturn',
    //         'balance',
    //         'invoiceDue'
    //     ));
    // }

    public function show($id)
{
    $payment = SupplierPayment::with(['supplier', 'purchase'])->findOrFail($id);
    $supplier = $payment->supplier;
    $supplier_id = $supplier->id;

    // ====== Ledger এর মতো একই ফর্মুলা ======
    $totalPurchase = \App\Models\Purchase::where('supplier_id', $supplier_id)
                        ->sum('subtotal');

    $totalPaid = \App\Models\SupplierPayment::where('supplier_id', $supplier_id)
                        ->sum('amount');

    $totalReturn = \App\Models\PurchaseReturn::where('supplier_id', $supplier_id)
                        ->sum('total_amount');

    $totalPaidWithReturn = $totalPaid + $totalReturn;

    $balance = $totalPurchase - $totalPaid - $totalReturn;

    // ====== এই Invoice এর আলাদা Due ======
    $invoiceDue = null;
    if ($payment->purchase) {
        $invoicePaid = \App\Models\SupplierPayment::where('purchase_id', $payment->purchase_id)
                            ->sum('amount');

        // ⚠️ purchase_returns এ purchase_id নেই, তাই invoice-level return বাদ
        $invoiceDue = $payment->purchase->subtotal - $invoicePaid;
    }

    return view('supplier_payment.show', compact(
        'payment',
        'totalPurchase',
        'totalPaid',
        'totalReturn',
        'totalPaidWithReturn',
        'balance',
        'invoiceDue'
    ));
}

    // Edit Payment
    public function edit($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        $suppliers = Supplier::orderBy('supplier_name')->get();
        return view('supplier_payment.edit', compact('payment','suppliers'));
    }

    // Update Payment
    public function update(Request $request, $id)
    {
        $payment = SupplierPayment::findOrFail($id);
        // Validation
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'amount'             => 'required|numeric|min:1',
            'payment_method'     => 'required|string',
            'payment_date'       => 'required|date',
            'cheque_number'      => 'nullable|string',
            'transaction_number' => 'nullable|string',
            // File (NOT required)
            'cheque_image' => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048',
            'deposit_slip' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $payment) {
            // Prepare Data
            $data = [
                'supplier_id' => $request->supplier_id,
                'purchase_id' => $request->purchase_id ?? null,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'note' => $request->purchase_id
                            ? 'Invoice Payment'
                            : 'Advance Payment',
                'cheque_number' => $request->cheque_number ?? null,
                'transaction_number' => $request->transaction_number ?? null,
            ];

            /* ===== File Upload ===== */

            // Cheque Image
            if ($request->hasFile('cheque_image')) {
                // (optional) delete old file
                if ($payment->cheque_image && \Storage::disk('public')->exists($payment->cheque_image)) {
                    \Storage::disk('public')->delete($payment->cheque_image);
                }
                $file = $request->file('cheque_image');
                $data['cheque_image'] = $file->store('cheques', 'public');
            }

            // Deposit Slip
            if ($request->hasFile('deposit_slip')) {
                // delete old
                if ($payment->deposit_slip && \Storage::disk('public')->exists($payment->deposit_slip)) {
                    \Storage::disk('public')->delete($payment->deposit_slip);
                }
                $file = $request->file('deposit_slip');
                $data['deposit_slip'] = $file->store('deposits', 'public');
            }

            /* ===== Update Payment ===== */
            $payment->update($data);
            /* ===== Recalculate Invoice ===== */
            if ($payment->purchase_id) {
                $purchase = Purchase::find($payment->purchase_id);
                if ($purchase) {
                    $totalPaid = $purchase->payments()->sum('amount');
                    $purchase->update([
                        'paid_amount' => $totalPaid,
                        'due_amount'  => $purchase->total_amount - $totalPaid,
                    ]);
                }
            }
        });
        return redirect()
            ->route('supplier.ledger', $payment->supplier_id)
            ->with('success', 'Payment Updated Successfully');
    }
    // Delete Payment
    public function destroy($id)
    {
        $payment = SupplierPayment::findOrFail($id);
        DB::transaction(function() use ($payment){
            $purchase_id = $payment->purchase_id;
            $payment->delete();
            // Adjust purchase due_amount if needed
            if($purchase_id){
                $purchase = Purchase::find($purchase_id);
                if($purchase){
                    $totalPaid = $purchase->payments()->sum('amount');
                    $purchase->update([
                        'paid_amount' => $totalPaid,
                        'due_amount' => $purchase->total_amount - $totalPaid
                    ]);
                }
            }
        });

        return back()->with('success','Payment Deleted Successfully');
    }

        public function supplierInvoices($id)
        {

        $purchases = Purchase::with('payments')
            ->where('supplier_id',$id)
            ->orderBy('date','desc')
            ->get()
            ->map(function($purchase){

                $paid = $purchase->payments->sum('amount');

                $due = $purchase->total_amount - $paid;

                return [
                    'id' => $purchase->id,
                    'Invoice_no' => $purchase->Invoice_no,
                    'due_amount' => $due
                ];

            })
            ->filter(function($purchase){
                return $purchase['due_amount'] > 0;
            })
            ->values();

        return response()->json($purchases);

        }

    public function reportPdf(Request $request)
    {
        $returns = PurchaseReturn::with(['items.product','supplier'])

            ->when($request->supplier_id, function($q) use($request){
                $q->where('supplier_id',$request->supplier_id);
            })

            ->when($request->product_id, function($q) use($request){
                $q->whereHas('items', function($qq) use($request){
                    $qq->where('product_id',$request->product_id);
                });
            })

            ->whereBetween('return_date',[
                $request->from_date,
                $request->to_date
            ])
            ->get();

        $totalQty = 0;

        foreach($returns as $r){
            foreach($r->items as $i){
                $totalQty += $i->qty;
            }
        }

        $pdf = Pdf::loadView(
            'purchase_returns.report_pdf',
            compact('returns','totalQty')
        )->setPaper('a4','portrait');

        return $pdf->download('purchase_return_report.pdf');
    }

    

}
