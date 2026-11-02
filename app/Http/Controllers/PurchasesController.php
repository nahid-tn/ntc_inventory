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
use App\Models\CompanyInfo;
use App\Models\Unit;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Carbon\Carbon;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Storage;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();
        $companyInfo = CompanyInfo::first();

        $query = Purchase::with([
            'supplier',
            'details.product',
            'supplierPayments'
        ]);

        if ($request->filled('invoice')) {
            $query->where(
                'Invoice_no',
                'like',
                '%' . $request->invoice . '%'
            );
        }

        if ($request->filled('supplier')) {
            $query->whereHas('supplier', function ($q) use ($request) {
                $q->where(
                    'supplier_name',
                    'like',
                    '%' . $request->supplier . '%'
                );
            });
        }

        if ($request->filled('product')) {
            $query->whereHas('details.product', function ($q) use ($request) {
                $q->where(
                    'product_name',
                    'like',
                    '%' . $request->product . '%'
                );
            });
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [
                $request->from,
                $request->to
            ]);
        }

        $purchaseIds = (clone $query)->pluck('id');

        $newPurchase = (clone $query)->sum('subtotal');

        $oldProductPurchases = DB::table(
            'old_product_purchase_details'
        )->sum('total_amount');

        $totalPurchase = $newPurchase + $oldProductPurchases;

        $oldPurchasesPayment = DB::table(
            'old_product_purchases'
        )->sum('total');

        $totalSupplierPayment = SupplierPayment::whereIn(
            'purchase_id',
            $purchaseIds
        )->sum('amount');

        $totalSupplierPaymentTSP = DB::table(
            'supplier_payments'
        )->sum('amount');

        $totalReturnAmount = PurchaseReturn::whereIn(
            'invoice_no',
            $purchaseIds
        )->sum('total_amount');

        $totalPaidWithTotalReturn =
            $totalSupplierPayment
            + $totalReturnAmount
            + $oldPurchasesPayment;

        $totalDue_D = $totalPurchase - $totalSupplierPaymentTSP;

        $totalPaymentReturn = $totalSupplierPaymentTSP + $totalReturnAmount;

        $totalDue = $totalPurchase - $totalPaymentReturn;

        $partialCount = (clone $query)->get()->filter(function ($purchase) {
            $paid = $purchase->supplierPayments->sum('amount');

            return $paid > 0 && $paid < $purchase->total_amount;
        })->count();

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
            'units',
            'companyInfo'
        ));
    }

    // public function PurchaseForm()
    // {
    //     $units = Unit::all();

    //     $suppliers = Supplier::where('status', 'active')
    //         ->orderBy('supplier_name')
    //         ->get();

    //     $products = Product::all();
    //     $companyInfo = CompanyInfo::first();

    //     return view('purchase.create', compact(
    //         'suppliers',
    //         'products',
    //         'units',
    //         'companyInfo'
    //     ));
    // }
    public function PurchaseForm()
    {
        $units = Unit::all();

        $suppliers = Supplier::where('status', 'active')
            ->orderBy('supplier_name')
            ->get();

        $products = Product::all();
        $companyInfo = CompanyInfo::first();

        return view('purchase.create', compact(
            'suppliers',
            'products',
            'units',
            'companyInfo'
        ));
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'date' => 'required|date',
    //         'payment_method' => 'required|string',
    //         'products' => 'required|array',
    //         'products.*.product_id' => 'required|exists:products,id',
    //         'products.*.qty' => 'required|numeric|min:1',
    //         'products.*.unit' => 'required|string',
    //         'products.*.price' => 'required|numeric|min:0',
    //         'challan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    //     ]);

    //     DB::transaction(function () use ($request) {

    //         // AUTO INVOICE
    //         $invoice = 'INV-' . str_pad(
    //             (Purchase::latest('id')->first()?->id ?? 0) + 1,
    //             6,
    //             '0',
    //             STR_PAD_LEFT
    //         );

    //         // CALCULATION
    //         $subtotal = collect($request->products)
    //             ->sum(fn ($p) => $p['qty'] * $p['price']);

    //         $taxPercent = $request->tax_percentage ?? 0;
    //         $taxAmount = ($subtotal * $taxPercent) / 100;
    //         $shipping = $request->shipping_amount ?? 0;
    //         $total = $subtotal + $taxAmount;
    //         // $total = $subtotal + $taxAmount + $shipping;

    //         // CREATE PURCHASE
    //         $purchase = Purchase::create([
    //             'Invoice_no' => $invoice,
    //             'purchase_order_no' => $request->purchase_order_no,
    //             'supplier_id' => $request->supplier_id,
    //             'date' => $request->date,
    //             'subtotal' => $subtotal,
    //             'tax_percentage' => $taxPercent,
    //             'tax_amount' => $taxAmount,
    //             'shipping_amount' => $shipping,
    //             'total_amount' => $total,
    //             'payment_method' => $request->payment_method,
    //         ]);

    //         $generator = new BarcodeGeneratorPNG();

    //         // SAVE DETAILS + STOCK + BARCODE
    //         foreach ($request->products as $item) {

    //             $detail = new PurchaseDetail();

    //             $detail->purchase_id = $purchase->id;
    //             $detail->product_id = $item['product_id'];
    //             $detail->qty = $item['qty'];
    //             $detail->unit = $item['unit'];
    //             $detail->price = $item['price'];
    //             $detail->total = $item['qty'] * $item['price'];

    //             // BARCODE PRICE = PURCHASE PRICE + 10%
    //             $barcodePrice = round(
    //                 (float) $item['price'] * 1.10,
    //                 2
    //             );

    //             // BARCODE DATA
    //             $barcodeData = implode('|', [
    //                 'PURCHASE_ID:' . $purchase->id,
    //                 'SUPPLIER_ID:' . $purchase->supplier_id,
    //                 'PRODUCT_ID:' . $item['product_id'],
    //                 'PURCHASE_PRICE:' . number_format(
    //                     $barcodePrice,
    //                     2,
    //                     '.',
    //                     ''
    //                 ),
    //             ]);

    //             // GENERATE BARCODE IMAGE
    //             $detail->barcode = base64_encode(
    //                 $generator->getBarcode(
    //                     $barcodeData,
    //                     $generator::TYPE_CODE_128
    //                 )
    //             );

    //             $detail->save();

    //             // STOCK UPDATE
    //             $product = Product::find($item['product_id']);

    //             if ($product) {
    //                 $product->increment('stock', $item['qty']);
    //             }
    //         }

    //         // CREATE INITIAL PAYMENT ONCE
    //         $paid = round(
    //             (float) ($request->paid_amount ?? 0),
    //             2
    //         );

    //         if ($paid > 0) {
    //             SupplierPayment::create([
    //                 'supplier_id' => $purchase->supplier_id,
    //                 'purchase_id' => $purchase->id,
    //                 'payment_date' => $request->date,
    //                 'amount' => $paid,
    //                 'BankName' => $request->BankName,
    //                 'account_number' => $request->account_number,
    //                 'cheque_number' => $request->cheque_number,
    //                 'transaction_number' => $request->transaction_number,
    //                 'note' => 'Initial Payment',
    //             ]);
    //         }

    //         // UPDATE PURCHASE PAID / DUE
    //         $totalPaid = SupplierPayment::where(
    //             'purchase_id',
    //             $purchase->id
    //         )->sum('amount');

    //         $purchase->update([
    //             'paid_amount' => $totalPaid,
    //             'due_amount' => round(
    //                 (float) $purchase->total_amount - (float) $totalPaid,
    //                 2
    //             ),
    //         ]);
    //     });

    //     return redirect()->route('purchase.index')
    //         ->with('success', 'Purchase Created Successfully');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'challan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            // AUTO INVOICE
            $invoice = 'INV-' . str_pad(
                (Purchase::latest('id')->first()?->id ?? 0) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            // CALCULATION
            $subtotal = collect($request->products)
                ->sum(fn ($p) => $p['qty'] * $p['price']);

            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount = ($subtotal * $taxPercent) / 100;
            $shipping = $request->shipping_amount ?? 0;
            $total = $subtotal + $taxAmount;
            // $total = $subtotal + $taxAmount + $shipping;

            // CREATE PURCHASE
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

            // SAVE PURCHASE CHALLAN FILE
            if ($request->hasFile('challan_file')) {
                $challanPath = $request->file('challan_file')
                    ->store('purchase_challans', 'public');

                $purchase->challan_file = $challanPath;
                $purchase->save();
            }

            $generator = new BarcodeGeneratorPNG();

            // SAVE DETAILS + STOCK + BARCODE
            foreach ($request->products as $item) {

                $detail = new PurchaseDetail();

                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $item['qty'];
                $detail->unit = $item['unit'];
                $detail->price = $item['price'];
                $detail->total = $item['qty'] * $item['price'];

                // BARCODE PRICE = PURCHASE PRICE + 10%
                $barcodePrice = round(
                    (float) $item['price'] * 1.10,
                    2
                );

                // BARCODE DATA
                $barcodeData = implode('|', [
                    'PURCHASE_ID:' . $purchase->id,
                    'SUPPLIER_ID:' . $purchase->supplier_id,
                    'PRODUCT_ID:' . $item['product_id'],
                    'PURCHASE_PRICE:' . number_format(
                        $barcodePrice,
                        2,
                        '.',
                        ''
                    ),
                ]);

                // GENERATE BARCODE IMAGE
                $detail->barcode = base64_encode(
                    $generator->getBarcode(
                        $barcodeData,
                        $generator::TYPE_CODE_128
                    )
                );

                $detail->save();

                // STOCK UPDATE
                $product = Product::find($item['product_id']);

                if ($product) {
                    $product->increment('stock', $item['qty']);
                }
            }

            // CREATE INITIAL PAYMENT ONCE
            $paid = round(
                (float) ($request->paid_amount ?? 0),
                2
            );

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

            // UPDATE PURCHASE PAID / DUE
            $totalPaid = SupplierPayment::where(
                'purchase_id',
                $purchase->id
            )->sum('amount');

            $purchase->update([
                'paid_amount' => $totalPaid,
                'due_amount' => round(
                    (float) $purchase->total_amount - (float) $totalPaid,
                    2
                ),
            ]);
        });

        return redirect()->route('purchase.index')
            ->with('success', 'Purchase Created Successfully');
    }

    public function edit($id)
    {
        $purchase = Purchase::with('details.product', 'supplier')
            ->findOrFail($id);

        $suppliers = Supplier::all();
        $products = Product::all();
        $units = Unit::all();
        $companyInfo = CompanyInfo::first();

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT PAYMENT FIX
        |--------------------------------------------------------------------------
        | paidAmount: Edit form-এর Initial Payment field-এ দেখানোর পরিমাণ।
        | totalPaidAmount: Initial + Additional সব পেমেন্টের যোগফল।
        |
        | paidAmount-এ সব পেমেন্টের যোগফল দিলে Update-এর সময় Additional Payment
        | আবার Initial Payment-এর মধ্যে যুক্ত হয়ে মোট পেমেন্ট বেড়ে যেতে পারে।
        */

        $paidAmount = (float) SupplierPayment::where(
            'purchase_id',
            $purchase->id
        )
            ->where('note', 'Initial Payment')
            ->sum('amount');

        $totalPaidAmount = (float) SupplierPayment::where(
            'purchase_id',
            $purchase->id
        )->sum('amount');

        $initialDue = round(
            (float) $purchase->total_amount - $totalPaidAmount,
            2
        );

        $totalDue = $initialDue;

        return view('purchase.edit', compact(
            'purchase',
            'suppliers',
            'products',
            'units',
            'paidAmount',
            'totalPaidAmount',
            'totalDue',
            'initialDue',
            'companyInfo'
        ));
    }

    // public function update(Request $request, $id)
    // {
    //     $validated = $request->validate([
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'date' => 'required|date',
    //         'payment_method' => 'required|string',
    //         'Invoice_no' => 'nullable|string|max:255',
    //         'purchase_order_no' => 'nullable|string|max:255',
    //         'tax_percentage' => 'nullable|numeric|min:0',
    //         'shipping_amount' => 'nullable|numeric|min:0',
    //         'paid_amount' => 'nullable|numeric|min:0',
    //         'BankName' => 'nullable|string|max:255',
    //         'account_number' => 'nullable|string|max:255',
    //         'cheque_number' => 'nullable|string|max:255',
    //         'transaction_number' => 'nullable|string|max:255',
    //         'products' => 'required|array|min:1',
    //         'products.*.product_id' => 'required|exists:products,id',
    //         'products.*.qty' => 'required|numeric|min:1',
    //         'products.*.unit' => 'required|string',
    //         'products.*.price' => 'required|numeric|min:0',
    //     ]);

    //     DB::transaction(function () use ($request, $id) {

    //         // FIND AND LOCK PURCHASE
    //         $purchase = Purchase::where('id', $id)
    //             ->lockForUpdate()
    //             ->firstOrFail();

    //         // REVERSE PREVIOUS STOCK
    //         $oldDetails = PurchaseDetail::where(
    //             'purchase_id',
    //             $purchase->id
    //         )->get();
    //         foreach ($oldDetails as $oldDetail) {
    //             $product = Product::where(
    //                 'id',
    //                 $oldDetail->product_id
    //             )->lockForUpdate()->first();

    //             if ($product) {
    //                 $product->decrement(
    //                     'stock',
    //                     (float) $oldDetail->qty
    //                 );
    //             }
    //         }

    //         // DELETE OLD PURCHASE DETAILS
    //         PurchaseDetail::where(
    //             'purchase_id',
    //             $purchase->id
    //         )->delete();

    //         // RECALCULATE PURCHASE TOTAL
    //         $subtotal = collect($request->products)->sum(
    //             fn ($item) => (float) $item['qty']
    //                 * (float) $item['price']
    //         );

    //         $taxPercent = (float) ($request->tax_percentage ?? 0);

    //         $taxAmount = round(
    //             ($subtotal * $taxPercent) / 100,
    //             2
    //         );

    //         $shipping = (float) ($request->shipping_amount ?? 0);
    //         $total = round(
    //             $subtotal + $taxAmount,
    //             2
    //         );

    //         // UPDATE PURCHASE
    //         $purchase->Invoice_no = $request->Invoice_no;
    //         $purchase->purchase_order_no = $request->purchase_order_no;
    //         $purchase->supplier_id = $request->supplier_id;
    //         $purchase->date = $request->date;
    //         $purchase->subtotal = $subtotal;
    //         $purchase->tax_percentage = $taxPercent;
    //         $purchase->tax_amount = $taxAmount;
    //         $purchase->shipping_amount = $shipping;
    //         $purchase->total_amount = $total;
    //         $purchase->payment_method = $request->payment_method;

    //         $purchase->save();

    //         $generator = new BarcodeGeneratorPNG();

    //         // CREATE NEW DETAILS + BARCODE + STOCK
    //         foreach ($request->products as $item) {

    //             $productId = (int) $item['product_id'];
    //             $qty = (float) $item['qty'];
    //             $price = (float) $item['price'];

    //             $product = Product::where('id', $productId)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             $detail = new PurchaseDetail();

    //             $detail->purchase_id = $purchase->id;
    //             $detail->product_id = $productId;
    //             $detail->qty = $qty;
    //             $detail->unit = $item['unit'];
    //             $detail->price = $price;
    //             $detail->total = round($qty * $price, 2);

    //             // BARCODE PRICE = PURCHASE PRICE + 10%
    //             $barcodePrice = round($price * 1.10, 2);

    //             // BARCODE DATA
    //             $barcodeData = implode('|', [
    //                 'PURCHASE_ID:' . $purchase->id,
    //                 'SUPPLIER_ID:' . $purchase->supplier_id,
    //                 'PRODUCT_ID:' . $productId,
    //                 'PURCHASE_PRICE:' . number_format(
    //                     $barcodePrice,
    //                     2,
    //                     '.',
    //                     ''
    //                 ),
    //             ]);

    //             // GENERATE BASE64 BARCODE IMAGE
    //             $detail->barcode = base64_encode(
    //                 $generator->getBarcode(
    //                     $barcodeData,
    //                     $generator::TYPE_CODE_128
    //                 )
    //             );

    //             $detail->save();

    //             // ADD STOCK AGAIN
    //             $product->increment('stock', $qty);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE INITIAL PAYMENT WITHOUT DUPLICATING ADDITIONAL PAYMENTS
    //         |--------------------------------------------------------------------------
    //         | Only the Initial Payment record is updated.
    //         | Additional Payment records are not modified.
    //         */

    //         $paid = round(
    //             (float) ($request->input('paid_amount') ?? 0),
    //             2
    //         );

    //         // Lock existing Initial Payment record
    //         $initialPayment = SupplierPayment::where(
    //             'purchase_id',
    //             $purchase->id
    //         )
    //             ->where('note', 'Initial Payment')
    //             ->lockForUpdate()
    //             ->first();

    //         $paymentData = [
    //             'supplier_id' => $purchase->supplier_id,
    //             'purchase_id' => $purchase->id,
    //             'payment_date' => $request->date,
    //             'amount' => $paid,
    //             'BankName' => $request->BankName,
    //             'account_number' => $request->account_number,
    //             'cheque_number' => $request->cheque_number,
    //             'transaction_number' => $request->transaction_number,
    //             'note' => 'Initial Payment',
    //         ];

    //         if ($initialPayment) {

    //             if ($paid > 0) {
    //                 // UPDATE existing payment; do not create another one
    //                 $initialPayment->update($paymentData);
    //             } else {
    //                 // Remove Initial Payment only when its amount is zero
    //                 $initialPayment->delete();
    //             }

    //         } elseif ($paid > 0) {

    //             // Create only when no Initial Payment record exists
    //             SupplierPayment::create($paymentData);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | RECALCULATE TOTAL PAID AND DUE
    //         |--------------------------------------------------------------------------
    //         | Sum all payment records, including Additional Payments.
    //         */

    //         $totalPaid = round(
    //             (float) SupplierPayment::where(
    //                 'purchase_id',
    //                 $purchase->id
    //             )->sum('amount'),
    //             2
    //         );

    //         $dueAmount = round(
    //             (float) $purchase->total_amount - $totalPaid,
    //             2
    //         );

    //         $purchase->update([
    //             'paid_amount' => $totalPaid,
    //             'due_amount' => $dueAmount,
    //         ]);
    //     });

    //     return redirect()
    //         ->route('purchase.index')
    //         ->with('success', 'Purchase updated successfully.');
    // }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'challan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'Invoice_no' => 'nullable|string|max:255',
            'purchase_order_no' => 'nullable|string|max:255',
            'tax_percentage' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'BankName' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:255',
            'transaction_number' => 'nullable|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {

            // FIND AND LOCK PURCHASE
            $purchase = Purchase::where('id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            // REVERSE PREVIOUS STOCK
            $oldDetails = PurchaseDetail::where(
                'purchase_id',
                $purchase->id
            )->get();
            foreach ($oldDetails as $oldDetail) {
                $product = Product::where(
                    'id',
                    $oldDetail->product_id
                )->lockForUpdate()->first();

                if ($product) {
                    $product->decrement(
                        'stock',
                        (float) $oldDetail->qty
                    );
                }
            }

            // DELETE OLD PURCHASE DETAILS
            PurchaseDetail::where(
                'purchase_id',
                $purchase->id
            )->delete();

            // RECALCULATE PURCHASE TOTAL
            $subtotal = collect($request->products)->sum(
                fn ($item) => (float) $item['qty']
                    * (float) $item['price']
            );

            $taxPercent = (float) ($request->tax_percentage ?? 0);

            $taxAmount = round(
                ($subtotal * $taxPercent) / 100,
                2
            );

            $shipping = (float) ($request->shipping_amount ?? 0);
            $total = round(
                $subtotal + $taxAmount,
                2
            );

            // UPDATE PURCHASE
            $purchase->Invoice_no = $request->Invoice_no;
            $purchase->purchase_order_no = $request->purchase_order_no;
            $purchase->supplier_id = $request->supplier_id;
            $purchase->date = $request->date;
            $purchase->subtotal = $subtotal;
            $purchase->tax_percentage = $taxPercent;
            $purchase->tax_amount = $taxAmount;
            $purchase->shipping_amount = $shipping;
            $purchase->total_amount = $total;
            $purchase->payment_method = $request->payment_method;

            // REPLACE CHALLAN ONLY WHEN A NEW FILE IS UPLOADED
            if ($request->hasFile('challan_file')) {
                $oldChallanPath = $purchase->challan_file;

                $newChallanPath = $request->file('challan_file')
                    ->store('purchase_challans', 'public');

                $purchase->challan_file = $newChallanPath;
            }

            $purchase->save();

            // Delete old challan after the new file path is saved
            if (!empty($oldChallanPath) &&
                $oldChallanPath !== ($purchase->challan_file ?? null) &&
                Storage::disk('public')->exists($oldChallanPath)) {
                Storage::disk('public')->delete($oldChallanPath);
            }

            $generator = new BarcodeGeneratorPNG();

            // CREATE NEW DETAILS + BARCODE + STOCK
            foreach ($request->products as $item) {

                $productId = (int) $item['product_id'];
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];

                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $detail = new PurchaseDetail();

                $detail->purchase_id = $purchase->id;
                $detail->product_id = $productId;
                $detail->qty = $qty;
                $detail->unit = $item['unit'];
                $detail->price = $price;
                $detail->total = round($qty * $price, 2);

                // BARCODE PRICE = PURCHASE PRICE + 10%
                $barcodePrice = round($price * 1.10, 2);

                // BARCODE DATA
                $barcodeData = implode('|', [
                    'PURCHASE_ID:' . $purchase->id,
                    'SUPPLIER_ID:' . $purchase->supplier_id,
                    'PRODUCT_ID:' . $productId,
                    'PURCHASE_PRICE:' . number_format(
                        $barcodePrice,
                        2,
                        '.',
                        ''
                    ),
                ]);

                // GENERATE BASE64 BARCODE IMAGE
                $detail->barcode = base64_encode(
                    $generator->getBarcode(
                        $barcodeData,
                        $generator::TYPE_CODE_128
                    )
                );

                $detail->save();

                // ADD STOCK AGAIN
                $product->increment('stock', $qty);
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE INITIAL PAYMENT WITHOUT DUPLICATING ADDITIONAL PAYMENTS
            |--------------------------------------------------------------------------
            | Only the Initial Payment record is updated.
            | Additional Payment records are not modified.
            */

            $paid = round(
                (float) ($request->input('paid_amount') ?? 0),
                2
            );

            // Lock existing Initial Payment record
            $initialPayment = SupplierPayment::where(
                'purchase_id',
                $purchase->id
            )
                ->where('note', 'Initial Payment')
                ->lockForUpdate()
                ->first();

            $paymentData = [
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'payment_date' => $request->date,
                'amount' => $paid,
                'BankName' => $request->BankName,
                'account_number' => $request->account_number,
                'cheque_number' => $request->cheque_number,
                'transaction_number' => $request->transaction_number,
                'note' => 'Initial Payment',
            ];

            if ($initialPayment) {

                if ($paid > 0) {
                    // UPDATE existing payment; do not create another one
                    $initialPayment->update($paymentData);
                } else {
                    // Remove Initial Payment only when its amount is zero
                    $initialPayment->delete();
                }

            } elseif ($paid > 0) {

                // Create only when no Initial Payment record exists
                SupplierPayment::create($paymentData);
            }

            /*
            |--------------------------------------------------------------------------
            | RECALCULATE TOTAL PAID AND DUE
            |--------------------------------------------------------------------------
            | Sum all payment records, including Additional Payments.
            */

            $totalPaid = round(
                (float) SupplierPayment::where(
                    'purchase_id',
                    $purchase->id
                )->sum('amount'),
                2
            );

            $dueAmount = round(
                (float) $purchase->total_amount - $totalPaid,
                2
            );

            $purchase->update([
                'paid_amount' => $totalPaid,
                'due_amount' => $dueAmount,
            ]);
        });

        return redirect()
            ->route('purchase.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function view($id)
    {
        $purchase = Purchase::with(
            'details.product',
            'supplier',
            'payments'
        )->findOrFail($id);

        $companyInfo = CompanyInfo::first();

        // USE SAVED BARCODE IMAGE DIRECTLY
        foreach ($purchase->details as $detail) {
            $detail->barcode_image = $detail->barcode ?: null;
        }

        return view(
            'purchase.view',
            compact('purchase', 'companyInfo')
        );
    }

    // Invoice PDF Download
    public function downloadPdf($id)
    {
        // LOAD PURCHASE DATA
        $purchase = Purchase::with([
            'details.product',
            'supplier',
            'payments'
        ])->findOrFail($id);

        // LOAD COMPANY INFO
        $companyInfo = CompanyInfo::first();

        // INVOICE BARCODE
        $generator = new BarcodeGeneratorPNG();

        $barcode = base64_encode(
            $generator->getBarcode(
                $purchase->Invoice_no . '|' .
                $purchase->supplier->supplier_name . '|NTC',
                $generator::TYPE_CODE_128
            )
        );

        // USE SAVED PRODUCT BARCODE IMAGE DIRECTLY
        foreach ($purchase->details as $detail) {
            $detail->barcode_image = $detail->barcode ?: null;
        }

        // RENDER VIEW
        $html = view('purchase.pdf', compact(
            'purchase',
            'barcode',
            'companyInfo'
        ))->render();

        // MPDF CONFIG
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,

            'fontDir' => array_merge($fontDirs, [
                public_path('fonts')
            ]),

            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],

            'default_font' => 'solaimanlipi',

            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output(
            'Purchase_Invoice_' . $purchase->Invoice_no . '.pdf',
            'D'
        );
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

    public function supplierDues(Request $request)
    {
        $purchases = Purchase::with([
            'supplier',
            'payments'
        ])->get();

        // Calculate due for each purchase
        // Shipping excluded + partial payments
        $purchases = $purchases->map(function ($purchase) {

            $totalWithoutShipping =
                ($purchase->total_amount ?? 0)
                - ($purchase->shipping_amount ?? 0);

            $paidSoFar = SupplierPayment::where(
                'purchase_id',
                $purchase->id
            )->sum('amount');

            $purchase->due_amount = $totalWithoutShipping - $paidSoFar;

            return $purchase;
        });

        $dues = $purchases->filter(function ($purchase) {
            return $purchase->due_amount > 0;
        });

        return view('purchase.supplier_dues', compact('dues'));
    }

    public function invoiceAutocomplete(Request $request)
    {
        $term = $request->term;

        $invoices = Purchase::where(
            'Invoice_no',
            'like',
            "%{$term}%"
        )->limit(10)->pluck('Invoice_no');

        return response()->json(
            $invoices->map(fn ($inv) => [
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

        SupplierPayment::create([
            'supplier_id' => $purchase->supplier_id,
            'purchase_id' => $purchase->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date ?? Carbon::now(),
            'note' => 'Additional Payment',
        ]);

        $totalPaid = SupplierPayment::where(
            'purchase_id',
            $purchase->id
        )->sum('amount');

        $purchase->update([
            'paid_amount' => $totalPaid,
            'due_amount' => round(
                (float) $purchase->total_amount - (float) $totalPaid,
                2
            ),
        ]);

        return redirect()->back()
            ->with('success', 'Payment added successfully');
    }

    public function invoiceInfo($id)
    {
        $purchase = Purchase::with('payments')->find($id);

        if (!$purchase) {
            return response()->json([
                'error' => 'Invoice not found'
            ], 404);
        }

        return response()->json([
            'Invoice_no' => $purchase->Invoice_no,

            'date' => $purchase->date
                ? $purchase->date->format('d-m-Y')
                : '',

            'total_amount' => $purchase->total_amount,
            'paid_amount' => $purchase->paid_amount,
            'due_amount' => $purchase->due_amount,

            'payments' => $purchase->payments->map(function ($p) {
                return [
                    'payment_date' => $p->payment_date
                        ? $p->payment_date->format('d-m-Y')
                        : '',

                    'amount' => $p->amount,
                    'payment_method' => $p->payment_method,
                ];
            }),
        ]);
    }

    public function showInvoice($id)
    {
        $purchase = Purchase::with('details.product')
            ->findOrFail($id);

        return view('purchase.invoice', compact('purchase'));
    }

    // Purchase Report PDF (Chronological)
    public function purchaseReportPdf(Request $request)
    {
        $supplier = null;

        if ($request->filled('supplier_id')) {
            $supplier = Supplier::find($request->supplier_id);

        } elseif ($request->filled('supplier')) {
            $supplier = Supplier::where(
                'supplier_name',
                'like',
                '%' . $request->supplier . '%'
            )->first();
        }

        $from = $request->from;
        $to = $request->to;

        // Opening Balance
        $openingPurchase = Purchase::when(
            $supplier,
            fn ($q) => $q->where('supplier_id', $supplier->id)
        )->when(
            $from,
            fn ($q) => $q->whereDate('date', '<', $from)
        )->sum('total_amount');

        $openingReturn = PurchaseReturn::when(
            $supplier,
            fn ($q) => $q->where('supplier_id', $supplier->id)
        )->when(
            $from,
            fn ($q) => $q->whereDate('return_date', '<', $from)
        )->sum('subtotal');

        $openingPayment = SupplierPayment::when(
            $supplier,
            fn ($q) => $q->where('supplier_id', $supplier->id)
        )->when(
            $from,
            fn ($q) => $q->whereDate('payment_date', '<', $from)
        )->sum('amount');

        $openingBalance = ($openingPayment + $openingReturn) - $openingPurchase;

        // Fetch Data
        $purchases = Purchase::with('details.product')
            ->when(
                $supplier,
                fn ($q) => $q->where('supplier_id', $supplier->id)
            )
            ->when(
                $from,
                fn ($q) => $q->whereDate('date', '>=', $from)
            )
            ->when(
                $to,
                fn ($q) => $q->whereDate('date', '<=', $to)
            )
            ->get();

        $returns = PurchaseReturn::with('items.product')
            ->when(
                $supplier,
                fn ($q) => $q->where('supplier_id', $supplier->id)
            )
            ->when(
                $from,
                fn ($q) => $q->whereDate('return_date', '>=', $from)
            )
            ->when(
                $to,
                fn ($q) => $q->whereDate('return_date', '<=', $to)
            )
            ->get();

        $payments = SupplierPayment::when(
            $supplier,
            fn ($q) => $q->where('supplier_id', $supplier->id)
        )->when(
            $from,
            fn ($q) => $q->whereDate('payment_date', '>=', $from)
        )->when(
            $to,
            fn ($q) => $q->whereDate('payment_date', '<=', $to)
        )->get();

        // Merge Transactions
        $transactions = [];

        foreach ($purchases as $p) {
            $transactions[] = [
                'type' => 'Purchase Invoice',
                'Particulars' => $p->transaction_number ?? '-',
                'date' => $p->date,
                'reference' => $p->Invoice_no,
                'debit' => $p->total_amount - ($p->shipping_amount ?? 0),
                'credit' => 0,
                'products' => $p->details,
            ];
        }

        foreach ($returns as $r) {
            $transactions[] = [
                'type' => 'Return Invoice',
                'Particulars' => $r->transaction_number
                    ?? $r->return_no
                    ?? '-',

                'date' => $r->return_date,
                'reference' => $r->invoice_no ?? '-',
                'debit' => 0,
                'credit' => $r->subtotal,
                'products' => $r->items,
            ];
        }

        foreach ($payments as $pay) {

            $paymentParticular = $pay->transaction_number
                ?? $pay->cheque_number
                ?? $pay->ref_no
                ?? '-';

            $transactions[] = [
                'type' => 'Payment',
                'Particulars' => $paymentParticular,
                'date' => $pay->payment_date,
                'reference' => $pay->ref_no ?? '-',
                'debit' => 0,
                'credit' => $pay->amount,
                'products' => [],
            ];
        }

        $transactions = collect($transactions)
            ->sortBy('date')
            ->values()
            ->toArray();

        // Running Balance
        $runningBalance = $openingBalance;

        foreach ($transactions as &$tx) {
            $runningBalance += ($tx['credit'] - $tx['debit']);
            $tx['running_balance'] = $runningBalance;
        }

        unset($tx);

        $closingBalance = $runningBalance;

        // mPDF
        $currentDate = Carbon::now()->format('Ymd');

        $fileName = 'Supplier_Of_'
            . ($supplier->id ?? 'All') . '_'
            . ($supplier->supplier_name ?? 'All') . '_'
            . $currentDate . '_ledger.pdf';

        $logo = '';

        if (file_exists(public_path('images/logo.png'))) {
            $logo = base64_encode(
                file_get_contents(public_path('images/logo.png'))
            );
        }

        $html = view('purchase.chronological_report_pdf', [
            'supplier' => $supplier,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'from_date' => $from,
            'to_date' => $to,
            'logo' => $logo,
        ])->render();

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html);

        return $mpdf->Output($fileName, 'I');
    }

    public function purchaseGroupReportPdf(Request $request)
    {
        try {

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            \Log::info('Purchase Group Report PDF Started');

            // Filters
            $from_date = $request->from;
            $to_date = $request->to;
            $supplierId = $request->supplier_id;
            $productId = $request->product_id;

            \Log::info([
                'Supplier' => $supplierId,
                'Product' => $productId,
                'From' => $from_date,
                'To' => $to_date,
            ]);

            // Company
            $company = CompanyInfo::first();

            // Selected Supplier
            $supplier = null;

            if (!empty($supplierId)) {
                $supplier = Supplier::find($supplierId);
            }

            // Selected Product
            $product = null;

            if (!empty($productId)) {
                $product = Product::find($productId);
            }

            // Purchase Query
            $query = Purchase::with([
                'supplier',
                'details.product',
                'supplierPayments'
            ]);

            if (!empty($supplierId)) {
                $query->where('supplier_id', $supplierId);
            }

            if (!empty($productId)) {
                $query->whereHas('details', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            }

            if (!empty($from_date)) {
                $query->whereDate('date', '>=', $from_date);
            }

            if (!empty($to_date)) {
                $query->whereDate('date', '<=', $to_date);
            }

            $purchases = $query
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            \Log::info('Total Purchases : ' . $purchases->count());

            if ($purchases->isEmpty()) {
                return back()->with('error', 'No Purchase Found.');
            }

            // Opening Balance
            $openingBalance = 0;
            $transactions = [];
            $runningBalance = $openingBalance;
            $grandTotal = 0;
            $grandPaid = 0;

            // Transaction Loop
            foreach ($purchases as $purchase) {

                $purchaseTotal = (float) ($purchase->total_amount ?? 0);

                $purchasePaid = (float) $purchase->supplierPayments
                    ->sum('amount');

                $grandTotal += $purchaseTotal;
                $grandPaid += $purchasePaid;

                // Product List
                $products = [];

                foreach ($purchase->details as $detail) {

                    if (!empty($productId)) {
                        if ($detail->product_id != $productId) {
                            continue;
                        }
                    }

                    $products[] = [
                        'product_id' => $detail->product_id,
                        'name' => optional($detail->product)->product_name ?? '-',
                        'qty' => (float) ($detail->qty ?? 0),
                        'price' => (float) ($detail->price ?? 0),
                        'total' => (float) (
                            $detail->total
                            ?? ($detail->qty * $detail->price)
                        ),
                    ];
                }

                if ($productId && count($products) == 0) {
                    continue;
                }

                $runningBalance += $purchaseTotal;

                $transactions[] = [
                    'date' => $purchase->date,
                    'reference' => $purchase->Invoice_no ?? '-',
                    'supplier' => optional($purchase->supplier)->supplier_name ?? '-',
                    'debit' => $purchaseTotal,
                    'credit' => $purchasePaid,
                    'running_balance' => $runningBalance,
                    'products' => $products,
                ];
            }

            // Summary
            $closingBalance = $runningBalance;
            $grandDue = $grandTotal - $grandPaid;

            \Log::info([
                'Transactions' => count($transactions),
                'Grand Total' => $grandTotal,
                'Grand Paid' => $grandPaid,
                'Grand Due' => $grandDue,
            ]);

            // Logo
            $logoBase64 = null;
            $logoPath = public_path('images/logo.png');

            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(
                    file_get_contents($logoPath)
                );
            }

            // Default Values
            $openingBalance = $openingBalance ?? 0;
            $transactions = $transactions ?? [];
            $closingBalance = $closingBalance ?? 0;
            $grandTotal = $grandTotal ?? 0;
            $grandPaid = $grandPaid ?? 0;
            $grandDue = $grandDue ?? 0;

            // Render HTML
            $html = view('purchase.group_report_pdf', compact(
                'company',
                'supplier',
                'product',
                'from_date',
                'to_date',
                'openingBalance',
                'transactions',
                'closingBalance',
                'grandTotal',
                'grandPaid',
                'grandDue',
                'logoBase64'
            ))->render();

            file_put_contents(
                storage_path('debug_purchase_group_report.html'),
                $html
            );

            // mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_top' => 8,
                'margin_bottom' => 10,
                'tempDir' => storage_path('app/mpdf'),
            ]);

            $mpdf->SetTitle('Purchase Group Report');
            $mpdf->SetAuthor(config('app.name'));
            $mpdf->SetDisplayMode('fullpage');

            $mpdf->SetHTMLFooter('
                <table width="100%" style="font-size:10px;border-top:1px solid #999;">
                    <tr>
                        <td width="50%">
                            Printed : ' . date('d-m-Y h:i A') . '
                        </td>
                        <td width="50%" align="right">
                            Page {PAGENO} of {nbpg}
                        </td>
                    </tr>
                </table>
            ');

            $mpdf->WriteHTML($html);

            return $mpdf->Output(
                'Purchase_Group_Report_' . date('Ymd_His') . '.pdf',
                'I'
            );

        } catch (\Throwable $e) {

            \Log::error('Purchase Group Report PDF Error');
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    private function createMpdf()
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'solaimanlipi',

            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),

            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                    'B' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ]
            ],

            'margin_top' => 15,
            'margin_right' => 10,
            'margin_bottom' => 15,
            'margin_left' => 10,

            'tempDir' => storage_path('app/temp'),
        ]);
    }
}
