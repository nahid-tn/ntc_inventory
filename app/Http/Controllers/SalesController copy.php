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
use App\Models\Category;

class SalesController extends Controller
{

public function index(Request $request)
{
    $query = Sales::with([
        'customer', 
        'details.product', 
        'oldProductPurchases.details.oldProduct',
        'receivedPayments'
    ]);

    // Invoice Search
    if ($request->filled('invoice')) {
        $query->where('invoice_no', 'like', '%' . $request->invoice . '%');
    }

    // Customer Search
    if ($request->filled('customer')) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('customer_name', 'like', '%' . $request->customer . '%')
              ->orWhere('mobile', 'like', '%' . $request->customer . '%');
        });
    }
    
    // Customer ID Search (from autocomplete)
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    }
    
    // Date Range Search
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('sale_date', [
            $request->from_date,
            $request->to_date
        ]);
    } elseif ($request->filled('from_date')) {
        $query->whereDate('sale_date', '>=', $request->from_date);
    } elseif ($request->filled('to_date')) {
        $query->whereDate('sale_date', '<=', $request->to_date);
    }

    // Product Search (autocomplete)
    if ($request->filled('product_id')) {
        $query->where(function ($q) use ($request) {
            $q->whereHas('details.product', function ($subQ) use ($request) {
                $subQ->where('id', $request->product_id);
            })->orWhereHas('oldProductPurchases.details.oldProduct', function ($subQ) use ($request) {
                $subQ->where('id', $request->product_id);
            });
        });
    }

    // Get sales with pagination
    $sales = $query->orderBy('sale_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(15)
                ->withQueryString();

    // ============================================
    // কাস্টমার ভিত্তিক মোট ডিউ (Customer Total Due)
    // ⭐ Overpayment হলে negative due হিসাবে ধরা হবে
    // ============================================
    $customerIds = $sales->pluck('customer_id')->filter()->unique()->values();
    $customerDueTotals = [];

    if ($customerIds->isNotEmpty()) {
        $allSalesForCustomers = Sales::whereIn('customer_id', $customerIds)
            ->with(['receivedPayments'])
            ->get();

        foreach ($allSalesForCustomers as $sale) {
            $paidFromSale     = $sale->paid_amount ?? 0;
            $paidFromReceived = $sale->receivedPayments->sum('amount') ?? 0;
            $totalPaid        = $paidFromSale + $paidFromReceived;

            // ⭐ Overpayment হলে negative due হবে
            $due = $sale->total_amount - $totalPaid;

            if (!isset($customerDueTotals[$sale->customer_id])) {
                $customerDueTotals[$sale->customer_id] = 0;
            }
            $customerDueTotals[$sale->customer_id] += $due;
        }
    }

    // ============================================
    // পুরো লিস্টের মোট ডিউ (Total Due)
    // ⭐ Overpayment হলে negative due হিসাবে ধরা হবে
    // ============================================
    $totalDue = Sales::with(['receivedPayments'])
        ->get()
        ->sum(function ($sale) {
            $paidFromSale     = $sale->paid_amount ?? 0;
            $paidFromReceived = $sale->receivedPayments->sum('amount') ?? 0;
            $totalPaid        = $paidFromSale + $paidFromReceived;

            // ⭐ Overpayment হলে negative due হবে
            return $sale->total_amount - $totalPaid;
        });

    // ============================================
    // মোট কাস্টমারের সংখ্যা
    // ============================================
    $totalCustomers = Customer::count();
    
    // ============================================
    // মোট সেলসের সংখ্যা
    // ============================================
    $totalSalesCount = Sales::count();
    
    // ============================================
    // আজকের সেলসের সংখ্যা ও টাকা
    // ============================================
    $todaySales = Sales::whereDate('sale_date', Carbon::today())->get();
    $todaySalesCount = $todaySales->count();
    $todaySalesAmount = $todaySales->sum('total_amount');
    
    // ============================================
    // ⭐ আজকের মোট আইটেম সংখ্যা (sale_details থেকে)
    // ============================================
    $todaySaleItems = \DB::table('sale_details')
        ->whereDate('created_at', Carbon::today())
        ->sum('qty');

    // ============================================
    // এই মাসের সেলসের সংখ্যা ও টাকা
    // ============================================
    $monthlySales = Sales::whereMonth('sale_date', Carbon::now()->month)
                        ->whereYear('sale_date', Carbon::now()->year)
                        ->get();
    $monthlySalesCount = $monthlySales->count();
    $monthlySalesAmount = $monthlySales->sum('total_amount');

    // ============================================
    // ফিল্টার অনুযায়ী মোট সেলসের সংখ্যা
    // ============================================
    $filteredSalesCount = (clone $query)->count();

    return view('sale.index', compact(
        'sales',
        'customerDueTotals',
        'totalDue',
        'totalCustomers',
        'totalSalesCount',
        'todaySaleItems',
        'todaySalesCount',
        'todaySalesAmount',
        'monthlySalesCount',
        'monthlySalesAmount',
        'filteredSalesCount'
    ));
}

public function createForm() {
    $products = Product::all();
    $customers = Customer::all();
    $units = Unit::all();
    $categorys = Category::all();
    $old_products = OldProduct::all();
    return view('sale.create', compact('products', 'customers', 'units', 'categorys', 'old_products'));
}
    
    public function store(Request $request)
{ 
    $request->validate([
        'invoice_no'     => 'nullable|string|unique:sales,invoice_no',
        'sale_date'      => 'required|date',
        'customer_id'    => 'required|exists:customers,id',
        'products'       => 'required|array|min:1',
        'products.*.product_name' => 'required|string',
        'products.*.qty'          => 'required|numeric|min:0',
        'products.*.unit'         => 'required|string',
        'products.*.price'        => 'required|numeric|min:0',
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
            // ⭐ Others হলে product_id খালি থাকবে, তবুও subtotal এ যোগ হবে
            if (empty($p['product_name'])) continue;
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
            if (empty($p['product_name'])) continue;

            $productId = $p['product_id'] ?? null;
            $isOthers  = !empty($p['is_others']) && $p['is_others'] == 1;

            // ⭐ Others হলে অথবা product_id না থাকলে নতুন Product তৈরি
            if ($isOthers || empty($productId)) {

                // ⭐ Auto-generate Product Code
                $lastProduct = Product::latest('id')->first();
                $nextId      = $lastProduct ? $lastProduct->id + 1 : 1;
                $productCode = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

                // ⭐ Duplicate check (race condition এড়ানোর জন্য)
                while (Product::where('product_code', $productCode)->exists()) {
                    $nextId++;
                    $productCode = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                }

                $newProduct = Product::create([
                    'product_code'     => $productCode,          // ✅ auto code
                    'product_name'     => $p['product_name'],
                    'unit'             => $p['unit'] ?? null,
                    'purchase_price'   => 0,
                    'wholesale_price'  => $p['price'] ?? 0,
                    'selling_price'    => $p['price'] ?? 0,
                    'stock'            => 0,
                    'is_active'        => true,
                ]);
                $productId = $newProduct->id;
            }

            SaleDetail::create([
                'sale_id'    => $sale->id,
                'product_id' => $productId,
                'qty'        => $p['qty'],
                'unit'       => $p['unit'],
                'price'      => $p['price'],
                'total'      => $p['qty'] * $p['price'],
                'narration'  => $p['narration'] ?? null
            ]);

            // ⭐ Only decrement stock for existing products (Others এর stock কমবে না)
            if (!$isOthers && !empty($productId)) {
                $product = Product::find($productId);
                if ($product) {
                    $product->stock -= $p['qty'];
                    $product->save();
                }
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
        'receivedPayments'
    ])->findOrFail($id);

    /*
    ============================================
    STEP 1 : ROLLBACK OLD PRODUCT STOCK
    ============================================
    */
    foreach ($sale->oldProductPurchases as $purchase) {
        foreach ($purchase->details as $detail) {
            if ($detail->oldProduct) {
                $detail->oldProduct->qty -= $detail->qty;
                if ($detail->oldProduct->qty < 0) {
                    $detail->oldProduct->qty = 0;
                }
                $detail->oldProduct->save();
            }
        }
    }

    /*
    ============================================
    STEP 2 : DELETE OLD DETAILS
    ============================================
    */

    SaleDetail::where('sale_id', $sale->id)->delete();

    foreach ($sale->oldProductPurchases as $purchase) {

        OldProductPurchaseDetail::where(
            'old_product_purchase_id',
            $purchase->id
        )->delete();

        $purchase->delete();
    }

    /*
    ============================================
    STEP 3 : CALCULATE TOTAL
    ============================================
    */

    $products = $request->products ?? [];
    $oldProducts = $request->old_products ?? [];
    $subtotal = 0;

    foreach ($products as $p) {

        if (empty($p['product_id'])) {
            continue;
        }

        $subtotal +=
            ($p['qty'] ?? 0)
            *
            ($p['price'] ?? 0);
    }

    $oldTotal = 0;

    foreach ($oldProducts as $op) {

        if (empty($op['product_name'])) {
            continue;
        }

        $oldTotal +=
            ($op['qty'] ?? 0)
            *
            ($op['price'] ?? 0);
    }

    $taxPercent = $request->tax_percentage ?? 0;
    $taxAmount = ($subtotal * $taxPercent) / 100;
    $shipping = $request->shipping_amount ?? 0;
    $discount = $request->discount_amount ?? 0;
    $grandTotal =
        max(
            $subtotal +
            $taxAmount +
            $shipping -
            $oldTotal -
            $discount,
            0
        );

    /*
    ============================================
    STEP 4 : UPDATE SALE
    ============================================
    */

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

    /*
    ============================================
    STEP 5 : INSERT PRODUCTS
    ============================================
    */

    foreach ($products as $p) {

        if (empty($p['product_id'])) {
            continue;
        }

        SaleDetail::create([

            'sale_id'    => $sale->id,
            'product_id' => $p['product_id'],

            'qty'        => $p['qty'] ?? 0,
            'unit'       => $p['unit'] ?? 'pcs',

            'price'      => $p['price'] ?? 0,

            'total'      =>
                ($p['qty'] ?? 0)
                *
                ($p['price'] ?? 0),

            'narration'  => $p['narration'] ?? null,
        ]);
    }

    /*
    ============================================
    STEP 6 : INSERT OLD PRODUCTS
    ============================================
    */

    if (!empty($oldProducts)) {

        $purchase = OldProductPurchase::create([

            'purchase_no' =>
                'OP-' .
                date('Ymd') .
                '-' .
                rand(1000, 9999),

            'sale_id'       => $sale->id,
            'total'         => $oldTotal,
            'purchase_date' => $request->sale_date,
        ]);

        foreach ($oldProducts as $op) {

            if (empty($op['product_name'])) {
                continue;
            }

            $oldProduct = OldProduct::firstOrCreate(
                [
                    'product_name' => $op['product_name']
                ],
                [
                    'qty'   => 0,
                    'unit'  => $op['unit'] ?? 'pcs',
                    'price' => 0,
                    'total' => 0
                ]
            );

            $oldProduct->qty += $op['qty'] ?? 0;
            $oldProduct->unit = $op['unit'] ?? $oldProduct->unit;
            $oldProduct->price = $op['price'] ?? $oldProduct->price;
            $oldProduct->total =
                $oldProduct->qty *
                $oldProduct->price;

            $oldProduct->save();

            OldProductPurchaseDetail::create([

                'old_product_purchase_id' => $purchase->id,

                'old_product_id' => $oldProduct->id,

                'qty' => $op['qty'] ?? 0,

                'unit' => $op['unit'] ?? 'pcs',

                'price' => $op['price'] ?? 0,

                'total_amount' =>
                    ($op['qty'] ?? 0)
                    *
                    ($op['price'] ?? 0),

                'narration' => $op['narration'] ?? null,
            ]);
        }
    }

    /*
    ============================================
    STEP 7 : PAYMENT ADJUSTMENT
    ============================================
    */

    $previousReceived =
        $sale->receivedPayments->sum('amount');

    $newPaid =
        (float) $request->paid_amount;

    if ($newPaid != $previousReceived) {

        $difference =
            $newPaid - $previousReceived;

        SalesReceived::create([

            'sale_id'         => $sale->id,
            'customer_id'     => $sale->customer_id,

            'received_date'   => $request->sale_date,

            'amount'          => abs($difference),

            'received_method' => $request->payment_method,

            'received_type'   =>
                $difference > 0
                    ? 'Adjustment Received'
                    : 'Partial Refund',

            'tracking_no'     => $request->ref_no,
        ]);
    }

    /*
    ============================================
    STEP 8 : UPDATE DUE
    ============================================
    */

    $totalReceived =
        $sale->refresh()
             ->receivedPayments
             ->sum('amount');

    $sale->update([

        'due_amount' =>
            $sale->total_amount -
            $totalReceived
    ]);
});

return redirect()
    ->route('sale.index')
    ->with(
        'success',
        'Sale Updated Successfully'
    );

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
            'amount' => 'required|numeric|min:0.01',
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
        $totalPaid = $sale->paid_amount + $totalReceived;
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

    $companyInfo = DB::table('company_info')->first();

    // Check if both copies should be printed
    $printBoth = request()->has('print_both') && request()->get('print_both') == 'true';

    // Previous Due Calculation
    $previousDue = 0;
    $previousSales = Sales::where('customer_id', $sale->customer_id)
        ->where('id', '!=', $sale->id)
        ->get();

    foreach ($previousSales as $prev) {
        $received = DB::table('sales_received')
            ->where('sale_id', $prev->id)
            ->sum('amount');
        $discount = $prev->discount_amount ?? 0;
        $net = ($prev->total_amount ?? 0) - $discount;
        $paid = ($prev->paid_amount ?? 0) + $received;
        $previousDue += max(0, $net - $paid);
    }

    $previousDue = (float) $previousDue;

    // Render View
    $html = view('sale.pdf', [
        'sale'          => $sale,
        'companyInfo'   => $companyInfo,
        'previousDue'   => $previousDue,
        'printBoth'     => $printBoth,
    ])->render();

    // =============================================
    // 🔥 MPDF Configuration
    // =============================================
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'fontDir' => array_merge(
            (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
            [public_path('fonts')]
        ),
        'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
            'solaimanlipi' => [
                'R' => 'SolaimanLipi.ttf',
                'useOTL' => 0,
                'useKashida' => 0,
            ]
        ],
        'default_font' => 'solaimanlipi',
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,
    ]);

    // ✅ CSS
    $mpdf->WriteHTML('
        <style>
            * { font-family: solaimanlipi, sans-serif !important; }
            body { font-family: solaimanlipi, sans-serif !important; }
        </style>
    ');

    $mpdf->WriteHTML($html);

    $invoice = $sale->invoice_no ?? $sale->id;
    $invoice = preg_replace('/[^A-Za-z0-9\-]/', '_', $invoice);

    return $mpdf->Output('Sale_' . $invoice . '.pdf', 'D');
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
    $discount = $sale->discount_amount ?? 0;
    $receivedTotal = $sale->receivedPayments->sum('amount') ?? 0;
    $netTotal = ($sale->total_amount ?? 0) - $discount;
    $totalPaid = ($sale->paid_amount ?? 0) + $receivedTotal;
    $currentDue = $netTotal - $totalPaid;

    // =========================
    // PREVIOUS DUE (ALL OLD SALES)
    // =========================
    $previousSales = Sales::where('customer_id', $sale->customer_id)
        ->where('id', '!=', $sale->id)
        ->get();

    $previousDue = 0;
    foreach ($previousSales as $prev) {
        $prevReceived = DB::table('sales_received')
            ->where('sale_id', $prev->id)
            ->sum('amount');
        $prevDiscount = $prev->discount_amount ?? 0;
        $prevNet = ($prev->total_amount ?? 0) - $prevDiscount;
        $prevPaid = ($prev->paid_amount ?? 0) + $prevReceived;
        $previousDue += max(0, $prevNet - $prevPaid);
    }

    $previousDue = (float) $previousDue;
    $totalDue = $previousDue + $currentDue;
    $oldProducts = collect();
    $oldTotal = 0;

    foreach ($sale->oldProductPurchases as $purchase) {
        foreach ($purchase->details as $item) {
            $qty = $item->qty ?? 0;
            $price = $item->price ?? 0;
            $line = $qty * $price;
            $oldTotal += $line;
            $oldProducts->push((object)[
                'product_name' => $item->oldProduct->product_name ?? 'Old Product',
                'qty'          => $qty,
                'price'        => $price,
                'total'        => $line,
            ]);
        }
    }

    // =========================
    // LAST 10 SALES (INCLUDING CURRENT)
    // =========================
    $lastSalesData = Sales::where('customer_id', $sale->customer_id)
        ->orderBy('sale_date', 'DESC')
        ->take(10)
        ->get()
        ->map(function ($s) use ($sale) {
            $received = DB::table('sales_received')
                ->where('sale_id', $s->id)
                ->sum('amount');
            $discount = $s->discount_amount ?? 0;
            $net = ($s->total_amount ?? 0);
            $paid = ($s->paid_amount ?? 0) + $received;
            return [
                'invoice' => $s->invoice_no,
                'date'    => $s->sale_date,
                'total'   => $net,
                'paid'    => $paid,
                'due'     => ($net - $paid),
                'is_current' => $s->id == $sale->id // বর্তমান চালান চিহ্নিত করতে
            ];
        });

    // =========================
    // VIEW
    // =========================
    $html = view('sale.partial_payment_pdf', [
        'sale'          => $sale,
        'companyInfo'   => $companyInfo,
        'discount'      => $discount,
        'receivedTotal' => $receivedTotal,
        'netTotal'      => $netTotal,
        'totalPaid'     => $totalPaid,
        'currentDue'    => $currentDue,
        'previousDue'   => $previousDue,
        'totalDue'      => $totalDue,
        'oldProducts'   => $oldProducts,
        'oldTotal'      => $oldTotal,
        'lastSalesData' => $lastSalesData,
    ])->render();

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
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
        'default_font' => 'solaimanlipi',
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,
    ]);

    $mpdf->WriteHTML($html);
    return response(
        $mpdf->Output('Partial_Payment_' . $sale->invoice_no . '.pdf', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Partial_Payment_' . $sale->invoice_no . '.pdf"',
        ]
    );
}
}