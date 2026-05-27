<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseDetail;
use App\Models\SupplierPayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\unit;
use Barryvdh\DomPDF\Facade\Pdf; // use this at top
use Carbon\Carbon;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        $query = Purchase::with([
            'supplier',
            'details.product',
            'supplierPayments'
        ]);

        // ================= INVOICE SEARCH =================
        if ($request->filled('invoice')) {
            $query->where('Invoice_no', 'like', '%' . $request->invoice . '%');
        }

        // ================= SUPPLIER SEARCH =================
        if ($request->filled('supplier')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where('supplier_name', 'like', '%' . $request->supplier . '%');
            });
        }

        // ================= PRODUCT SEARCH =================
        if ($request->filled('product')) {
            $query->whereHas('details.product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->product . '%');
            });
        }

        // ================= DATE RANGE =================
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        // ================= PURCHASE IDS =================
        $purchaseIds = (clone $query)->pluck('id');

        // ================= TOTAL PURCHASE =================
        $newPurchase = (clone $query)->sum('subtotal');
        $oldProductPurchases = DB::table('old_product_purchase_details')->sum('total_amount'); 
        $totalPurchase = $newPurchase + $oldProductPurchases;

        $oldPurchasesPayment= DB::table('old_product_purchases')->sum('total');

        // ================= TOTAL PAYMENT =================
        $totalSupplierPayment = SupplierPayment::whereIn('purchase_id', $purchaseIds)
            ->sum('amount');
        $totalSupplierPaymentTSP = DB::table('supplier_payments')->sum('amount');
        // ================= TOTAL RETURN =================
        $totalReturnAmount = PurchaseReturn::whereIn('invoice_no', $purchaseIds)
            ->sum('total_amount');
    //     $totalReturnAmount = PurchaseReturn::whereIn('purchase_return_id', $purchaseIds)
    // ->sum('total_amount');

        // ================= TOTAL PAID =================
        $totalPaidWithTotalReturn = $totalSupplierPayment + $totalReturnAmount + $oldPurchasesPayment;
        $totalDue_D= $totalPurchase - $totalSupplierPaymentTSP;
        $totalPaymentReturn= $totalSupplierPaymentTSP + $totalReturnAmount;
        // ================= TOTAL DUE =================
        $totalDue = $totalPurchase - $totalPaymentReturn;

        // ================= PARTIAL PAYMENT COUNT =================
        $partialCount = (clone $query)->get()->filter(function ($purchase) {

            $paid = $purchase->supplierPayments->sum('amount');

            return $paid > 0 && $paid < $purchase->total_amount;

        })->count();

        // ================= PURCHASE LIST =================
        $purchases = $query
            ->withSum('supplierPayments as total_paid', 'amount')
            ->orderBy('date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('purchase.index', compact(
            'purchases',
            'totalPurchase',
            'totalSupplierPayment',
            'totalReturnAmount',
            'totalPaidWithTotalReturn',
            'totalSupplierPaymentTSP',
            'totalDue_D',
            'totalPaymentReturn',
            'newPurchase',
            'oldProductPurchases',
            'oldPurchasesPayment',
            'totalDue',
            'partialCount',
            'units'
        ));
    }

    public function PurchaseForm()
    {
        $units = Unit::all();
        $suppliers = Supplier::where('status','active')
        ->orderBy('supplier_name')->get();
        $products  = Product::all();
        return view('purchase.create', compact('suppliers', 'products','units'));
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'date'        => 'required|date',
    //         'payment_method' => 'required|string',
    //         'products.*.product_id' => 'required|exists:products,id',
    //         'products.*.qty'  => 'required|numeric|min:1',
    //         'products.*.unit' => 'required|string',
    //         'products.*.price'=> 'required|numeric|min:0',
    //     ]);

    //     DB::transaction(function () use ($request) {

    //         // Auto invoice
    //         $invoice = $request->Invoice ?? 'INV-' . str_pad((Purchase::latest('id')->first()?->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

    //         // Calculate subtotal, tax, total
    //         $subtotal = collect($request->products)->sum(fn($p) => $p['qty'] * $p['price']);
    //         $taxPercent = $request->tax_percentage ?? 0;
    //         $taxAmount  = ($subtotal * $taxPercent) / 100;
    //         $shipping   = $request->shipping_amount ?? 0;
    //         $total = $subtotal + $taxAmount + $shipping;

    //         // Create purchase
    //         $purchase = Purchase::create([
    //             'Invoice_no'      => $invoice,
    //             'purchase_order_no'=> $request->purchase_order_no,
    //             'supplier_id'     => $request->supplier_id,
    //             'date'            => $request->date,
    //             'subtotal'        => $subtotal,
    //             'tax_percentage'  => $taxPercent,
    //             'tax_amount'      => $taxAmount,
    //             'shipping_amount' => $shipping,
    //             'total_amount'    => $total,
    //             'payment_method'  => $request->payment_method,
    //         ]);

    //         // Save purchase details & update stock
    //         foreach ($request->products as $item) {
    //             PurchaseDetail::create([
    //                 'purchase_id' => $purchase->id,
    //                 'product_id'  => $item['product_id'],
    //                 'qty'         => $item['qty'],
    //                 'unit'        => $item['unit'],
    //                 'price'       => $item['price'],
    //                 'total'       => $item['qty'] * $item['price'],
    //             ]);

    //             Product::where('id', $item['product_id'])
    //                 ->increment('stock', $item['qty']);
    //         }

    //         // Optional initial payment
    //         $paid = (float) ($request->paid_amount ?? 0);
    //         if ($paid > 0) {
    //             SupplierPayment::create([
    //                 'supplier_id'   => $purchase->supplier_id,
    //                 'purchase_id'   => $purchase->id,
    //                 'payment_date'  => $request->date,
    //                 'amount'        => $paid,
    //                 'BankName'      => $request->BankName ?? null,
    //                 'account_number'=> $request->account_number ?? null,
    //                 'cheque_number' => $request->cheque_number ?? null,
    //                 'transaction_number' => $request->transaction_number ?? null,
    //                 'note'          => 'Initial Purchase Payment',
    //             ]);
    //         }
    //     });

    //     return redirect()->route('purchase.index')->with('success', 'Purchase Created Successfully');
    // }
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',

            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            // Auto Invoice
            $invoice = 'INV-' . str_pad(
                (Purchase::latest('id')->first()?->id ?? 0) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            // Calculation
            $subtotal = collect($request->products)->sum(fn($p) => $p['qty'] * $p['price']);

            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount = ($subtotal * $taxPercent) / 100;

            $shipping = $request->shipping_amount ?? 0;

            $total = $subtotal + $taxAmount + $shipping;

            // Create Purchase
            $purchase = Purchase::create([
                'Invoice_no' => $invoice,
                'purchase_order_no' => $request->purchase_order_no,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercent,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shipping,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
            ]);

            // Save Details + Update Stock
            foreach ($request->products as $item) {

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit' => $item['unit'],
                    'price' => $item['price'],
                    'total' => $item['qty'] * $item['price'],
                ]);

                // ✅ STOCK INCREASE FIX
                $product = Product::find($item['product_id']);

                if ($product) {
                    $product->increment('stock', $item['qty']);
                }
            }

            // Payment
            $paid = (float) ($request->paid_amount ?? 0);

            if ($paid > 0) {
                SupplierPayment::create([
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'payment_date' => $request->date,
                    'amount' => $paid,
                    'BankName' => $request->BankName,
                    'account_number' => $request->account_number,
                    'cheque_number' => $request->cheque_number,
                    'transaction_number' => $request->transaction_number,
                    'note' => 'Initial Payment',
                ]);
            }
        });

        return redirect()->route('purchase.index')
            ->with('success', 'Purchase Created Successfully');
    }
    
    private function calculateSubtotal($products)
    {
        $subtotal = 0;
        foreach ($products as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }
        return $subtotal;
    }

    public function edit($id)
    {
        $purchase = Purchase::with('details.product','supplier')->findOrFail($id);

        $suppliers = Supplier::all();
        $products  = Product::all();
        $units     = Unit::all();

        // SupplierPayment থেকে Paid Amount
        $paidAmount = SupplierPayment::where('purchase_id', $purchase->id)
                        ->sum('amount');

        // Due Calculation
        $totalDue = $purchase->total_amount - $paidAmount;

        return view('purchase.edit', compact(
            'purchase',
            'suppliers',
            'products',
            'units',
            'paidAmount',
            'totalDue'
        ));
    }

   
    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',

            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {

            $purchase = Purchase::with('details')->findOrFail($id);

            // 🔴 STEP 1: Reverse Old Stock
            foreach ($purchase->details as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->decrement('stock', $oldItem->qty);
                }
            }

            // 🔴 STEP 2: Delete Old Details
            $purchase->details()->delete();

            // 🔴 STEP 3: Delete Old Payment
            SupplierPayment::where('purchase_id', $purchase->id)->delete();

            // 🔵 STEP 4: Recalculate totals (same as store)
            $subtotal = collect($request->products)->sum(fn($p) => $p['qty'] * $p['price']);

            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount = ($subtotal * $taxPercent) / 100;

            $shipping = $request->shipping_amount ?? 0;

            $total = $subtotal + $taxAmount + $shipping;

            // 🔵 STEP 5: Update Purchase
            $purchase->update([
                'Invoice_no' => $request->Invoice_no ?? $purchase->Invoice_no, // keep old if not given
                'purchase_order_no' => $request->purchase_order_no,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercent,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shipping,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
            ]);

            // 🔵 STEP 6: Insert New Details + Update Stock
            foreach ($request->products as $item) {

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit' => $item['unit'],
                    'price' => $item['price'],
                    'total' => $item['qty'] * $item['price'],
                ]);

                // ✅ STOCK UPDATE (same as store)
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('stock', $item['qty']);
                }
            }

            // 🔵 STEP 7: Save Payment Again
            $paid = (float) ($request->paid_amount ?? 0);

            if ($paid > 0) {
                SupplierPayment::create([
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'payment_date' => $request->date,
                    'amount' => $paid,
                    'BankName' => $request->BankName,
                    'account_number' => $request->account_number,
                    'cheque_number' => $request->cheque_number,
                    'transaction_number' => $request->transaction_number,
                    'note' => 'Purchase Payment Updated',
                ]);
            }
        });

        return redirect()
            ->route('purchase.index')
            ->with('success', 'Purchase Updated Successfully');
    }

    public function view($id)
    {
        $purchase = Purchase::with('details.product', 'supplier', 'payments')->findOrFail($id);
        return view('purchase.view', compact('purchase'));
    }

    public function downloadPdf($id)
    {
        $purchase = Purchase::with('details.product', 'supplier')->findOrFail($id);
        $pdf = Pdf::loadView('purchase.pdf', compact('purchase'));
        return $pdf->download('Purchase_Invoice_'.$purchase->Invoice_no.'.pdf');
    }

    public function destroy($id)
    {
    DB::transaction(function () use ($id) {
        $purchase = Purchase::with('details')->findOrFail($id);
        foreach ($purchase->details as $item) {
            Product::where('id', $item->product_id)
                ->decrement('stock', $item->qty);
        }
        $purchase->details()->delete();
        $purchase->delete();
    });

    return redirect()->route('purchase.index')
        ->with('success', 'Purchase Deleted Successfully');
    }

    // public function supplierDues(Request $request)
    // {
    //     $query = Purchase::with('supplier')
    //         ->where('due_amount', '>', 0)
    //         ->where(function ($q) use ($request) {
    //             if ($request->supplier) {
    //                 $q->orWhereHas('supplier', function ($s) use ($request) {
    //                     $s->where('supplier_name', 'like', '%' . $request->supplier . '%');
    //                 });
    //             }
    //             if ($request->invoice) {
    //                 $q->orWhere('Invoice_no', 'like', '%' . $request->invoice . '%');
    //             }
    //             if ($request->from && $request->to) {
    //                 $q->orWhereBetween('date', [$request->from, $request->to]);
    //             }
    //         });
    //     $dues = $query->orderBy('date')->get();
    //     return view('purchase.supplier_dues', compact('dues'));
    // }

        public function supplierDues(Request $request)
{
    $purchases = Purchase::with(['supplier', 'payments'])->get();

    // Calculate due for each purchase (shipping excluded + partial payments)
    $purchases = $purchases->map(function($purchase){

        $totalWithoutShipping = ($purchase->total_amount ?? 0) - ($purchase->shipping_amount ?? 0);

        $paidSoFar = SupplierPayment::where('purchase_id', $purchase->id)->sum('amount');

        $purchase->due_amount = $totalWithoutShipping - $paidSoFar;

        return $purchase;
    });

    // Filter only dues > 0
    $dues = $purchases->filter(function($purchase){
        return $purchase->due_amount > 0;
    });

    return view('purchase.supplier_dues', compact('dues'));
}

    public function invoiceAutocomplete(Request $request)
    {
        $term = $request->term;
        $invoices = Purchase::where('Invoice_no', 'like', "%{$term}%")
            ->limit(10)
            ->pluck('Invoice_no');
        if ($request->invoice) {
        $query->where('Invoice_no', 'like', '%' . $request->invoice . '%');
        }
        return response()->json(
            $invoices->map(fn($inv) => [
                'label' => $inv,
                'value' => $inv,
            ])
        );
    }

    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date' => 'nullable|date',
        ]);
        $purchase = Purchase::findOrFail($id);
        $payment = SupplierPayment::create([
            'supplier_id'    => $purchase->supplier_id,
            'purchase_id'    => $purchase->id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date'   => $request->payment_date ?? Carbon::now(),
            'note'           => 'Additional Payment',
        ]);
        $totalPaid = $purchase->payments()->sum('amount');
        $purchase->update([
            'paid_amount' => $totalPaid,
            'due_amount'  => $purchase->total_amount - $totalPaid,
        ]);
        return redirect()->back()->with('success','Payment added successfully');
    }
    
    public function invoiceInfo($id)
    {
        $purchase = Purchase::with('payments')->find($id);
        if (!$purchase) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        return response()->json([
            'Invoice_no'   => $purchase->Invoice_no,
            'date'         => $purchase->date ? $purchase->date->format('d-m-Y') : '',
            'total_amount' => $purchase->total_amount,
            'paid_amount'  => $purchase->paid_amount,
            'due_amount'   => $purchase->due_amount,
            'payments'     => $purchase->payments->map(function($p){
                return [
                    'payment_date'   => $p->payment_date ? $p->payment_date->format('d-m-Y') : '',
                    'amount'         => $p->amount,
                    'payment_method' => $p->payment_method,
                ];
            }),
        ]);
    }
    public function showInvoice($id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);
        return view('purchase.invoice', compact('purchase'));
    }

    public function purchaseReportPdf(Request $request)
    {
        $supplier = null;

        if ($request->filled('supplier_id')) {
            $supplier = Supplier::find($request->supplier_id);
        } elseif ($request->filled('supplier')) {
            $supplier = Supplier::where('supplier_name', 'like', '%'.$request->supplier.'%')->first();
        }

        $from = $request->from;
        $to   = $request->to;

        /* ================= Opening Balance ================= */

        $openingPurchase = Purchase::when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('date','<',$from))
            ->sum('total_amount');

        $openingReturn = PurchaseReturn::when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('return_date','<',$from))
            ->sum('total_amount');

        $openingPayment = SupplierPayment::when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('payment_date','<',$from))
            ->sum('amount');

        // Credit - Debit
        $openingBalance = ($openingPayment + $openingReturn) - $openingPurchase;


        /* ================= Fetch Data ================= */

        $purchases = Purchase::with('items.product')
            ->when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('date','>=',$from))
            ->when($to, fn($q)=>$q->whereDate('date','<=',$to))
            ->get();

        $returns = PurchaseReturn::with('items.product')
            ->when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('return_date','>=',$from))
            ->when($to, fn($q)=>$q->whereDate('return_date','<=',$to))
            ->get();

        $payments = SupplierPayment::when($supplier, fn($q)=>$q->where('supplier_id',$supplier->id))
            ->when($from, fn($q)=>$q->whereDate('payment_date','>=',$from))
            ->when($to, fn($q)=>$q->whereDate('payment_date','<=',$to))
            ->get();
        /* ================= Merge Transactions ================= */

        $transactions = [];


        /* Purchases */
        foreach($purchases as $p){
            $transactions[] = [
                'type'        => 'Purchase Invoice',
                'Particulars' => $p->transaction_number ?? '-',
                'date'        => $p->date,
                'reference'   => $p->Invoice_no,
                'debit' => $p->total_amount - ($p->shipping_amount ?? 0),
                'credit'      => 0,
                'products'    => $p->items,
            ];
        }


        /* Returns */
        foreach($returns as $r){
            $transactions[] = [
                'type'        => 'Return Invoice',
                'Particulars' => $r->transaction_number ?? $r->return_no ?? '-',
                'date'        => $r->return_date,
                'reference'   => $r->invoice_no ?? '-',
                'debit'       => 0,
                'credit'      => $r->total_amount,
                'products'    => $r->items,
            ];
        }


        /* Payments */
        foreach($payments as $pay){
            $paymentParticular =
                $pay->transaction_number
            ?? $pay->cheque_number
            ?? $pay->ref_no
            ?? '-';

            $transactions[] = [
                'type'        => 'Payment',
                'Particulars' => $paymentParticular,
                'date'        => $pay->payment_date,
                'reference'   => $pay->ref_no ?? '-',
                'debit'       => 0,
                'credit'      => $pay->amount,
                'products'    => [],
            ];
        }

        /* ================= Sort ================= */

        $transactions = collect($transactions)
            ->sortBy('date')
            ->values()
            ->toArray();


        /* ================= Running Balance ================= */

        $runningBalance = $openingBalance;

        foreach($transactions as &$tx){
            // Credit - Debit
            $runningBalance += ($tx['credit'] - $tx['debit']);
            $tx['running_balance'] = $runningBalance;
        }

        $closingBalance = $runningBalance;
        /* ================= PDF ================= */
        $currentDate = Carbon::now()->format('Ymd');
        $fileName = 'Supplier_Of_' . $supplier->id . '_' .$supplier->supplier_name. '_' . $currentDate . '_' . 'ledger.pdf';
        $logo = base64_encode(file_get_contents(public_path('images/logo.png')));
        $pdf = Pdf::loadView('purchase.chronological_report', [
            'supplier'       => $supplier,
            'transactions'   => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'from_date'      => $from,
            'to_date'        => $to,
            'logo'           => $logo,   // 👈 এখানে পাঠাতে হবে
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,
        ]);

        return $pdf->download($fileName);
    }
}