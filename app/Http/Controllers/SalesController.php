<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Models\OldProduct;
use App\Models\OldProductPurchase;
use App\Models\OldProductPurchaseDetail;
use App\Models\SalesReceived;
use Carbon\Carbon;
use App\Models\Unit;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sales::with([
            'customer', 
            'details.product', 
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'  // ✅ যোগ করা হয়েছে
        ]);

        // Invoice Search
        if ($request->invoice) {
            $query->where('invoice_no', $request->invoice);
        }

        // Customer Search
        if ($request->customer) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customer . '%')
                ->orWhere('mobile', 'like', '%' . $request->customer . '%');
            });
        }
        
        // Date Range Search
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('sale_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // Product Search (autocomplete)
        if ($request->product_id) {
            $query->whereHas('details.product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            })->orWhereHas('oldProductPurchases.details.oldProduct', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')
                    ->paginate(15)
                    ->withQueryString();

        return view('sale.index', compact('sales'));
    }

    public function createForm() {
        $products = Product::all();
        $customers = Customer::all();
        $units = Unit::all();
        $old_products = OldProduct::all();
        return view('sale.create', compact('products', 'customers', 'units', 'old_products'));
    }
    
    public function store(Request $request)
    { 
        $request->validate([
            'invoice_no'     => 'nullable|string|unique:sales,invoice_no',
            'sale_date'      => 'required|date',
            'customer_id'    => 'required|exists:customers,id',
            // 'payment_method' => 'required',
        ]);

        DB::transaction(function () use ($request) {

            /* ================= Invoice Number ================= */
            $invoiceNo = $request->invoice_no;

            if (empty($invoiceNo)) {
                $lastInvoice = Sales::latest()->first();
                $invoiceNo = $lastInvoice
                    ? 'INV-' . str_pad($lastInvoice->id + 1, 6, '0', STR_PAD_LEFT)
                    : 'INV-000001';
            }

            /* ================= New Products ================= */
            $products = $request->products ?? [];
            $subtotal = 0;

            foreach ($products as $p) {
                if (empty($p['product_id'])) continue;
                $subtotal += ($p['qty'] * $p['price']);
            }

            /* ================= Old Products ================= */
            $oldProducts = $request->old_products ?? [];
            $oldTotal = 0;

            foreach ($oldProducts as $op) {
                if (empty($op['product_name'])) continue;
                $oldTotal += ($op['qty'] * $op['price']);
            }

            /* ================= Calculation ================= */
            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount  = ($subtotal * $taxPercent) / 100;
            $shipping   = $request->shipping_amount ?? 0;
            $discount   = $request->discount_amount ?? 0;
            $grandTotal = $subtotal + $taxAmount + $shipping - $oldTotal - $discount;
            $grandTotal = max($grandTotal, 0);

            /* ================= Create Sale ================= */
            $sale = Sales::create([
                'invoice_no'      => $invoiceNo,
                'sale_date'       => $request->sale_date,
                'customer_id'     => $request->customer_id,
                'subtotal'        => $subtotal,
                'tax_percentage'  => $taxPercent,
                'tax_amount'      => $taxAmount,
                'shipping_amount' => $shipping,
                'discount_amount' => $discount,
                'total_amount'    => $grandTotal,
                'due_amount'      => $grandTotal,
                'ref_no'          => $request->ref_no,
                'body_date'       => $request->body_date,
            ]);

            /* ================= Store New Products ================= */
            foreach ($products as $p) {
                if (empty($p['product_id'])) continue;

                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $p['product_id'],
                    'qty'        => $p['qty'],
                    'unit'       => $p['unit'],
                    'price'      => $p['price'],
                    'total'      => $p['qty'] * $p['price'],
                    'narration'  => $p['narration'] ?? null
                ]);

                $product = Product::find($p['product_id']);
                if ($product) {
                    $product->stock -= $p['qty'];
                    $product->save();
                }
            }

            /* ================= Store Old Products ================= */
            if (!empty($oldProducts)) {
                $lastId = OldProductPurchase::max('id') ?? 0;
                $purchaseNo = 'OP-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                $purchase = OldProductPurchase::create([
                    'purchase_no'   => $purchaseNo,
                    'sale_id'       => $sale->id,
                    'total'         => $oldTotal,
                    'purchase_date' => $request->sale_date,
                ]);

                foreach ($oldProducts as $op) {
                    if (empty($op['product_name'])) continue;

                    $oldProduct = OldProduct::firstOrNew([
                        'product_name' => $op['product_name']
                    ]);

                    $oldProduct->qty += $op['qty'] ?? 0;
                    $oldProduct->unit = $op['unit'] ?? null;
                    $oldProduct->price = $op['price'] ?? 0;
                    $oldProduct->total = $oldProduct->qty * ($op['price'] ?? 0);
                    $oldProduct->sale_id = $sale->id;
                    $oldProduct->save();

                    OldProductPurchaseDetail::create([
                        'old_product_purchase_id' => $purchase->id,
                        'old_product_id'          => $oldProduct->id,
                        'qty'                     => $op['qty'] ?? 0,
                        'unit'                    => $op['unit'] ?? null,
                        'price'                   => $op['price'] ?? 0,
                        'total_amount'            => ($op['qty'] ?? 0) * ($op['price'] ?? 0),
                        'narration'               => $op['narration'] ?? null,
                    ]);
                }
            }

            /* ================= Initial Payment ================= */
            $paid = (float) ($request->paid_amount ?? 0);

            if ($paid > 0) {
                $receivedType = ($paid >= $grandTotal)
                    ? 'Sales Received'
                    : 'Partial Payment';

                SalesReceived::create([
                    'sale_id'         => $sale->id,
                    'customer_id'     => $sale->customer_id,
                    'received_date'   => $request->sale_date,
                    'amount'          => $paid,
                    'received_method' => $request->payment_method,
                    'received_type'   => $receivedType,
                    'tracking_no'     => $request->ref_no,
                ]);

                $totalReceived = SalesReceived::where('sale_id', $sale->id)->sum('amount');
                $sale->update([
                    'due_amount' => $sale->total_amount - $totalReceived
                ]);
            }
        });

        return redirect()->route('sale.index')
            ->with('success', 'Sale Created Successfully!');
    }

    public function edit($id)
    {
        $sale = Sales::with([
            'details.product',
            'customer',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'  // ✅ already added
        ])->findOrFail($id);
        
        $units = Unit::all();
        
        // ✅ Eager loading already done, so no N+1 here
        $lastPayment = $sale->receivedPayments->last(); // Changed from latest() to avoid extra query
        
        $old_products = OldProduct::all();
        return view('sale.edit', compact('sale','lastPayment', 'old_products', 'units'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'invoice_no'     => 'required|string|unique:sales,invoice_no,' . $id,
            'sale_date'      => 'required|date',
            'customer_id'    => 'required|exists:customers,id',
            'payment_method' => 'required',
        ]);

        DB::transaction(function () use ($request, $id) {
            $sale = Sales::with([
                'details.product', 
                'oldProductPurchases.details.oldProduct',
                'receivedPayments'  // ✅ added for eager loading
            ])->findOrFail($id);

            /* =====================================================
            STEP 1: ROLLBACK STOCK (Previous Sale)
            ======================================================*/
            foreach ($sale->details as $detail) {
                if ($detail->product) {
                    $detail->product->stock += $detail->qty;
                    $detail->product->save();
                }
            }

            /* =====================================================
            STEP 2: ROLLBACK OLD PRODUCTS
            ======================================================*/
            foreach ($sale->oldProductPurchases as $purchase) {
                foreach ($purchase->details as $detail) {
                    if ($detail->oldProduct) {
                        $detail->oldProduct->qty -= $detail->qty;
                        $detail->oldProduct->save();
                    }
                }
            }

            /* =====================================================
            STEP 3: DELETE OLD DETAILS
            ======================================================*/
            SaleDetail::where('sale_id', $sale->id)->delete();
            foreach ($sale->oldProductPurchases as $purchase) {
                OldProductPurchaseDetail::where('old_product_purchase_id', $purchase->id)->delete();
                $purchase->delete();
            }

            /* =====================================================
            STEP 4: CALCULATE NEW VALUES
            ======================================================*/
            $products    = $request->products ?? [];
            $oldProducts = $request->old_products ?? [];

            $subtotal = 0;
            foreach ($products as $p) {
                if (empty($p['product_id'])) continue;
                $subtotal += ($p['qty'] ?? 0) * ($p['price'] ?? 0);
            }

            $oldTotal = 0;
            foreach ($oldProducts as $op) {
                if (empty($op['product_name'])) continue;
                $oldTotal += ($op['qty'] ?? 0) * ($op['price'] ?? 0);
            }

            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount  = ($subtotal * $taxPercent) / 100;
            $shipping   = $request->shipping_amount ?? 0;
            $discount   = $request->discount_amount ?? 0;
            $grandTotal = max($subtotal + $taxAmount + $shipping - $oldTotal - $discount, 0);

            /* =====================================================
            STEP 5: UPDATE SALE
            ======================================================*/
            $sale->update([
                'invoice_no'      => $request->invoice_no,
                'sale_date'       => $request->sale_date,
                'customer_id'     => $request->customer_id,
                'subtotal'        => $subtotal,
                'tax_percentage'  => $taxPercent,
                'tax_amount'      => $taxAmount,
                'shipping_amount' => $shipping,
                'discount_amount' => $discount,
                'total_amount'    => $grandTotal,
                'ref_no'          => $request->ref_no,
                'body_date'       => $request->body_date,
            ]);

            /* =====================================================
            STEP 6: STORE NEW PRODUCTS + STOCK DEDUCT
            ======================================================*/
            foreach ($products as $p) {
                if (empty($p['product_id'])) continue;
                $product = Product::find($p['product_id']);
                if (!$product) continue;
                if (($product->stock ?? 0) < ($p['qty'] ?? 0)) {
                    throw new \Exception("Insufficient stock for {$product->product_name}");
                }
                $product->stock -= $p['qty'] ?? 0;
                $product->save();
                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $p['product_id'],
                    'qty'        => $p['qty'] ?? 0,
                    'unit'       => $p['unit'] ?? 'pcs',
                    'price'      => $p['price'] ?? 0,
                    'total'      => ($p['qty'] ?? 0) * ($p['price'] ?? 0),
                    'narration'  => $p['narration'] ?? null,
                ]);
            }

            /* =====================================================
            STEP 7: STORE OLD PRODUCTS
            ======================================================*/
            if (!empty($oldProducts)) {
                $purchase = OldProductPurchase::create([
                    'purchase_no'   => 'OP-' . date('Ymd') . '-' . rand(1000,9999),
                    'sale_id'       => $sale->id,
                    'total'         => $oldTotal,
                    'purchase_date' => $request->sale_date,
                ]);

                foreach ($oldProducts as $op) {
                    if (empty($op['product_name'])) continue;
                    $oldProduct = OldProduct::firstOrCreate(
                        ['product_name' => $op['product_name']],
                        ['qty'=>0, 'unit'=>$op['unit'] ?? 'pcs', 'price'=>0, 'total'=>0]
                    );

                    $oldProduct->qty   += $op['qty'] ?? 0;
                    $oldProduct->unit   = $op['unit'] ?? $oldProduct->unit;
                    $oldProduct->price  = $op['price'] ?? $oldProduct->price;
                    $oldProduct->total  = $oldProduct->qty * $oldProduct->price;
                    $oldProduct->save();

                    OldProductPurchaseDetail::create([
                        'old_product_purchase_id' => $purchase->id,
                        'old_product_id'          => $oldProduct->id,
                        'qty'                     => $op['qty'] ?? 0,
                        'unit'                    => $op['unit'] ?? 'pcs',
                        'price'                   => $op['price'] ?? 0,
                        'total_amount'            => ($op['qty'] ?? 0) * ($op['price'] ?? 0),
                        'narration'               => $op['narration'] ?? null,
                    ]);
                }
            }

            /* =====================================================
            STEP 8: PAYMENT DIFFERENCE LOGIC
            ======================================================*/
            // ✅ Using eager loaded collection instead of query
            $previousReceived = $sale->receivedPayments->sum('amount');
            $newPaid = (float) $request->paid_amount;
            
            if ($newPaid != $previousReceived) {
                $difference = $newPaid - $previousReceived;
                if ($difference != 0) {
                    SalesReceived::create([
                        'sale_id'         => $sale->id,
                        'customer_id'     => $sale->customer_id,
                        'received_date'   => $request->sale_date,
                        'amount'          => abs($difference),
                        'received_method' => $request->payment_method,
                        'received_type'   => $difference > 0 
                            ? 'Adjustment Received'
                            : 'Partial Refund',
                        'tracking_no'     => $request->ref_no,
                    ]);
                }
            }

            /* =====================================================
            FINAL DUE UPDATE
            ======================================================*/
            // ✅ Using eager loaded collection
            $totalReceived = $sale->refresh()->receivedPayments->sum('amount');
            $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);
        });

        return redirect()->route('sale.index')
            ->with('success', 'Sale Updated Successfully!');
    }

    public function view($id)
    {
        $sale = Sales::with([
            'customer',
            'details.product',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'  // ✅ already added
        ])->findOrFail($id);
        
        return view('sale.view', compact('sale'));
    }
  
    // Sale Edit + Partial Payment Page
    public function editPartial($id)
    {
        $sale = Sales::with([
            'customer',
            'details.product',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'  // ✅ added
        ])->findOrFail($id);

        return view('sale.edit_partial', compact('sale'));
    }

    // Partial Payment Save
    public function savePartialPayment(Request $request, $id)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:1',
            'received_date'   => 'required|date',
            'received_method' => 'required|string',
            'tracking_no'     => 'nullable|string',
        ]);

        $sale = Sales::with('receivedPayments')->findOrFail($id);  // ✅ added eager loading

        $method = $request->received_method;
        if($method === 'other'){
            $method = $request->received_method_other;
        }

        SalesReceived::create([
            'sale_id'         => $sale->id,
            'customer_id'     => $sale->customer_id,
            'received_date'   => $request->received_date,
            'amount'          => $request->amount,
            'received_method' => $method,
            'tracking_no'     => $request->tracking_no,
            'received_type'   => 'Partial Payment',
        ]);

        // ✅ Using eager loaded collection
        $totalReceived = $sale->refresh()->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);

        return redirect()->back()->with('success', 'Partial payment added successfully!');
    }

    public function updatePartialPayment(Request $request, $id)
    {
        $payment = SalesReceived::with('sale')->findOrFail($id);  // ✅ eager load sale
        
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'received_date' => 'required|date',
            'received_method' => 'required|string',
            'tracking_no' => 'nullable|string',
        ]);

        $payment->update([
            'amount' => $request->amount,
            'received_date' => $request->received_date,
            'received_method' => $request->received_method,
            'received_method_other' => $request->received_method=='other' ? $request->received_method_other : null,
            'tracking_no' => $request->tracking_no,
        ]);

        // Update due_amount in Sale
        $sale = $payment->sale;
        $sale->load('receivedPayments');  // ✅ eager load before summing
        $totalReceived = $sale->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);

        return back()->with('success', 'Partial Payment updated successfully.');
    }

    public function deletePartialPayment($id)
    {
        $payment = SalesReceived::with('sale')->findOrFail($id);  // ✅ eager load sale
        $sale = $payment->sale;
        $payment->delete();

        $sale->load('receivedPayments');  // ✅ reload eager loaded relation
        $totalReceived = $sale->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);

        return back()->with('success', 'Partial Payment deleted successfully.');
    }

    

    public function destroy($id) 
    {
        $sale = Sales::with('details.product')->findOrFail($id);  // ✅ added eager loading
        
        DB::transaction(function() use($sale) {
            foreach($sale->details as $item) {
                $product = $item->product;
                $product->stock += $item->qty;
                $product->save();
            }
            $sale->details()->delete();
            $sale->delete();
        });
        
        return redirect()->route('sale.index');
    }

    public function autocomplete(Request $request)
    {
        $term = $request->term;

        $customers = Customer::where('customer_name', 'LIKE', "%{$term}%")
            ->orWhere('mobile', 'LIKE', "%{$term}%")
            ->orWhere('address', 'LIKE', "%{$term}%")
            ->get(['id', 'customer_name', 'mobile', 'address']);

        $result = $customers->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->customer_name,
                'mobile' => $c->mobile,
                'address' => $c->address,
            ];
        });

        return response()->json($result);
    }

    public function saleAutocomplete(Request $request)
    {
        $term = $request->term;
        
        $sales = Sales::with('customer')  // ✅ already has eager loading
            ->where(function ($query) use ($term) {
                $query->whereRaw("CAST(invoice_no AS CHAR) LIKE ?", ["%{$term}%"])
                    ->orWhereHas('customer', function ($q) use ($term) {
                        $q->where('customer_name', 'LIKE', "%{$term}%");
                    });
            })
            ->limit(10)
            ->get();
            
        return response()->json($sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'sale_date' => $sale->sale_date,
                'customer_name' => $sale->customer->customer_name ?? '',
                'label' => "Invoice: {$sale->invoice_no} | Date: {$sale->sale_date} | Customer: " . ($sale->customer->customer_name ?? ''),
                'value' => $sale->invoice_no . ' - ' . ($sale->customer->customer_name ?? ''),
            ];
        }));
    }

    public function receivePayment(Request $request, $id)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:1',
            'received_method' => 'required|string',
            'received_date'   => 'required|date',
            'tracking_no'     => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $sale = Sales::with('receivedPayments')->findOrFail($id);  // ✅ added eager loading

            $totalReceived = $sale->receivedPayments->sum('amount');  // ✅ using collection instead of query
            $due = $sale->total_amount - $totalReceived;
            $amount = (float)$request->amount;

            if ($amount == 0) {
                abort(422, 'Amount cannot be zero');
            }

            SalesReceived::create([
                'sale_id'       => $sale->id,
                'customer_id'   => $sale->customer_id,
                'received_date' => $request->received_date,
                'amount'        => $amount,
                'received_method' => $request->received_method,
                'received_type'   => 'Partial Received',
                'tracking_no'     => $request->tracking_no,
            ]);

            $newTotalReceived = $sale->refresh()->receivedPayments->sum('amount');
            $sale->update([
                'due_amount' => $sale->total_amount - $newTotalReceived,
            ]);
        });

        return redirect()->route('sale.index')
            ->with('success', 'Payment received successfully.');
    }

    public function downloadPdf($id)
    {
        $sale = Sales::with([
            'customer',
            'details.product',
            'receivedPayments',
            'oldProductPurchases.details.oldProduct',
        ])->findOrFail($id);

        $html = view('sale.pdf', [
            'sale' => $sale
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'solaimanlipi',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'autoPageBreak' => true,
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('sale_'.$sale->invoice_no.'.pdf', 'D');
    }

    public function partialPaymentPdf($id)
{
    $sale = Sales::with([
        'customer',
        'details.product',
        'oldProductPurchases.details.oldProduct',
        'receivedPayments'
    ])->findOrFail($id);

    $companyInfo = DB::table('company_info')->first();

    //discount calculation
    // ✅ Discount (DB থেকে)
    $discount = $sale->discount ?? 0;

    // ✅ Received Payment
    $receivedTotal = $sale->receivedPayments->sum('amount');

    // ✅ Net Total
    $netTotal = $sale->total_amount - $discount;

    // ✅ Total Paid
    $totalPaid = $sale->paid_amount + $receivedTotal;

    // ✅ Due
    $dueAmount = $netTotal - $totalPaid;


    $html = view(
        'sale.partial_payment_pdf',
        compact('sale', 'companyInfo', 'discount', 'receivedTotal', 'netTotal', 'totalPaid', 'dueAmount')
    )->render();

    // ✅ SINGLE PROPER MPDF INSTANCE (FIXED)
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',

        // ⭐ IMPORTANT FOR BANGLA
        'autoScriptToLang' => true,
        'autoLangToFont' => true,

        'fontDir' => array_merge(
            (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
            [public_path('fonts')]
        ),

        'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
            'solaimanlipi' => [
                'R' => 'SolaimanLipi.ttf',
            ]
        ],

        // ⭐ MUST MATCH fontdata key
        'default_font' => 'solaimanlipi',

        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,
    ]);

    $mpdf->WriteHTML($html);

    return response(
        $mpdf->Output(
            'Partial_Payment_'.$sale->invoice_no.'.pdf',
            'S'
        ),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Partial_Payment.pdf"',
        ]
    );
}
}