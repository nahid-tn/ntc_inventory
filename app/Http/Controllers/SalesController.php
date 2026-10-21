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
use App\Models\CompanyInfo;

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

        if ($request->filled('invoice')) {
            $query->where('invoice_no', 'like', '%' . $request->invoice . '%');
        }

        if ($request->filled('customer')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->customer . '%')
                  ->orWhere('mobile', 'like', '%' . $request->customer . '%');
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('sale_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        if ($request->filled('product_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('details.product', function ($subQ) use ($request) {
                    $subQ->where('id', $request->product_id);
                })->orWhereHas('oldProductPurchases.details.oldProduct', function ($subQ) use ($request) {
                    $subQ->where('id', $request->product_id);
                });
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate(15)
                    ->withQueryString();

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
                $due = $sale->total_amount - $totalPaid;

                if (!isset($customerDueTotals[$sale->customer_id])) {
                    $customerDueTotals[$sale->customer_id] = 0;
                }
                $customerDueTotals[$sale->customer_id] += $due;
            }
        }

        $totalDue = Sales::with(['receivedPayments'])
            ->get()
            ->sum(function ($sale) {
                $paidFromSale     = $sale->paid_amount ?? 0;
                $paidFromReceived = $sale->receivedPayments->sum('amount') ?? 0;
                $totalPaid        = $paidFromSale + $paidFromReceived;
                return $sale->total_amount - $totalPaid;
            });

        $totalCustomers  = Customer::count();
        $totalSalesCount = Sales::count();

        $todaySales = Sales::whereDate('sale_date', Carbon::today())->get();
        $todaySalesCount  = $todaySales->count();
        $todaySalesAmount = $todaySales->sum('total_amount');

        $todaySaleItems = \DB::table('sale_details')
            ->whereDate('created_at', Carbon::today())
            ->sum('qty');

        $monthlySales = Sales::whereMonth('sale_date', Carbon::now()->month)
                            ->whereYear('sale_date', Carbon::now()->year)
                            ->get();
        $monthlySalesCount  = $monthlySales->count();
        $monthlySalesAmount = $monthlySales->sum('total_amount');

        $filteredSalesCount = (clone $query)->count();

        return view('sale.index', compact(
            'sales', 'customerDueTotals', 'totalDue', 'totalCustomers',
            'totalSalesCount', 'todaySaleItems', 'todaySalesCount',
            'todaySalesAmount', 'monthlySalesCount', 'monthlySalesAmount',
            'filteredSalesCount'
        ));
    }

    public function createForm()
    {
    $products = Product::with('lastPurchaseDetail')->get();
    $customers    = Customer::all();
    $units        = Unit::all();
    $categorys    = Category::all();
    $old_products = OldProduct::all();

    return view('sale.create', compact(
        'products',
        'customers',
        'units',
        'categorys',
        'old_products'
    ));
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

            'note'            => 'nullable|string',
            'status'          => 'nullable|in:pending,completed,cancelled',
            'body_date'       => 'nullable|date',
            'warranty_period' => 'nullable|string|max:255',
            'payment_method'  => 'nullable|string',
            'received_type'   => 'nullable|string',
            ]);

        DB::transaction(function () use ($request) {

            $invoiceNo = $request->invoice_no;
            if (empty($invoiceNo)) {
                $lastInvoice = Sales::latest()->first();
                $invoiceNo = $lastInvoice
                    ? 'INV-' . str_pad($lastInvoice->id + 1, 6, '0', STR_PAD_LEFT)
                    : 'INV-000001';
            }

            $products = $request->products ?? [];
            $subtotal = 0;
            foreach ($products as $p) {
                if (empty($p['product_name'])) continue;
                $subtotal += ($p['qty'] * $p['price']);
            }

            $oldProducts = $request->old_products ?? [];
            $oldTotal = 0;
            foreach ($oldProducts as $op) {
                if (empty($op['product_name'])) continue;
                $oldTotal += ($op['qty'] * $op['price']);
            }

            $taxPercent = $request->tax_percentage ?? 0;
            $taxAmount  = ($subtotal * $taxPercent) / 100;
            $shipping   = $request->shipping_amount ?? 0;
            $discount   = $request->discount_amount ?? 0;
            $grandTotal = $subtotal + $taxAmount + $shipping - $oldTotal - $discount;
            $grandTotal = max($grandTotal, 0);

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
                'received_type'   => $request->received_type ?? 'Sales Received',
                'payment_method'  => $request->payment_method,
                'due_amount'      => $grandTotal,
                'note'            => $request->note,
                'status'          => $request->status ?? 'pending',
                'body_date'       => $request->body_date ?: null,
                'warranty_period' => $request->warranty_period ?: null,
            ]);

            foreach ($products as $p) {
                if (empty($p['product_name'])) continue;

                $productId = $p['product_id'] ?? null;
                $isOthers  = !empty($p['is_others']) && $p['is_others'] == 1;

                if ($isOthers || empty($productId)) {
                    $lastProduct = Product::latest('id')->first();
                    $nextId      = $lastProduct ? $lastProduct->id + 1 : 1;
                    $productCode = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

                    while (Product::where('product_code', $productCode)->exists()) {
                        $nextId++;
                        $productCode = 'PRD-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                    }

                    $newProduct = Product::create([
                        'product_code'     => $productCode,
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

                if (!$isOthers && !empty($productId)) {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->stock -= $p['qty'];
                        $product->save();
                    }
                }
            }

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

                    $oldProduct = OldProduct::firstOrNew(['product_name' => $op['product_name']]);
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

           
                $paid = (float) ($request->paid_amount ?? 0);

                if ($paid > 0) {

                    $paymentType = ($paid >= $grandTotal)
                        ? 'Full'
                        : 'Partial Payment';

                    SalesReceived::create([
                        'sale_id'         => $sale->id,
                        'customer_id'     => $sale->customer_id,
                        'received_date'   => $request->sale_date,
                        'amount'          => $paid,
                        'received_method' => $request->payment_method,
                        'payment_type'    => $paymentType,
                        'tracking_no'     => $request->note,
                    ]);

                    $totalReceived = SalesReceived::where('sale_id', $sale->id)
                        ->sum('amount');

                    $sale->update([
                        'due_amount' => $sale->total_amount - $totalReceived,
                    ]);
                }

        });

        return redirect()->route('sale.index')->with('success', 'Sale Created Successfully!');
    }

    public function edit($id)
    {
        $sale = Sales::with([
            'details.product',
            'customer',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'
        ])->findOrFail($id);

        $units = Unit::all();
        $lastPayment = $sale->receivedPayments->last();
        $old_products = OldProduct::all();
        return view('sale.edit', compact('sale','lastPayment', 'old_products', 'units'));
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'invoice_no'     => 'required|string|unique:sales,invoice_no,' . $id,
    //         'sale_date'      => 'required|date',
    //         'customer_id'    => 'required|exists:customers,id',
    //         'payment_method' => 'required',
    //     ]);

    //     DB::transaction(function () use ($request, $id) {

    //         $sale = Sales::with([
    //             'details.product',
    //             'oldProductPurchases.details.oldProduct',
    //             'receivedPayments'
    //         ])->findOrFail($id);

    //         foreach ($sale->oldProductPurchases as $purchase) {
    //             foreach ($purchase->details as $detail) {
    //                 if ($detail->oldProduct) {
    //                     $detail->oldProduct->qty -= $detail->qty;
    //                     if ($detail->oldProduct->qty < 0) {
    //                         $detail->oldProduct->qty = 0;
    //                     }
    //                     $detail->oldProduct->save();
    //                 }
    //             }
    //         }

    //         SaleDetail::where('sale_id', $sale->id)->delete();

    //         foreach ($sale->oldProductPurchases as $purchase) {
    //             OldProductPurchaseDetail::where('old_product_purchase_id', $purchase->id)->delete();
    //             $purchase->delete();
    //         }

    //         $products = $request->products ?? [];
    //         $oldProducts = $request->old_products ?? [];
    //         $subtotal = 0;

    //         foreach ($products as $p) {
    //             if (empty($p['product_id'])) continue;
    //             $subtotal += ($p['qty'] ?? 0) * ($p['price'] ?? 0);
    //         }

    //         $oldTotal = 0;
    //         foreach ($oldProducts as $op) {
    //             if (empty($op['product_name'])) continue;
    //             $oldTotal += ($op['qty'] ?? 0) * ($op['price'] ?? 0);
    //         }

    //         $taxPercent = $request->tax_percentage ?? 0;
    //         $taxAmount = ($subtotal * $taxPercent) / 100;
    //         $shipping = $request->shipping_amount ?? 0;
    //         $discount = $request->discount_amount ?? 0;
    //         $grandTotal = max($subtotal + $taxAmount + $shipping - $oldTotal - $discount, 0);

    //         $sale->update([
    //             'invoice_no'      => $request->invoice_no,
    //             'sale_date'       => $request->sale_date,
    //             'customer_id'     => $request->customer_id,
    //             'subtotal'        => $subtotal,
    //             'tax_percentage'  => $taxPercent,
    //             'tax_amount'      => $taxAmount,
    //             'shipping_amount' => $shipping,
    //             'discount_amount' => $discount,
    //             'total_amount'    => $grandTotal,
    //             'note'          => $request->note,
    //             'body_date'       => $request->body_date,
    //         ]);

    //         foreach ($products as $p) {
    //             if (empty($p['product_id'])) continue;
    //             SaleDetail::create([
    //                 'sale_id'    => $sale->id,
    //                 'product_id' => $p['product_id'],
    //                 'qty'        => $p['qty'] ?? 0,
    //                 'unit'       => $p['unit'] ?? 'pcs',
    //                 'price'      => $p['price'] ?? 0,
    //                 'total'      => ($p['qty'] ?? 0) * ($p['price'] ?? 0),
    //                 'narration'  => $p['narration'] ?? null,
    //             ]);
    //         }

    //         if (!empty($oldProducts)) {
    //             $purchase = OldProductPurchase::create([
    //                 'purchase_no'   => 'OP-' . date('Ymd') . '-' . rand(1000, 9999),
    //                 'sale_id'       => $sale->id,
    //                 'total'         => $oldTotal,
    //                 'purchase_date' => $request->sale_date,
    //             ]);

    //             foreach ($oldProducts as $op) {
    //                 if (empty($op['product_name'])) continue;

    //                 $oldProduct = OldProduct::firstOrCreate(
    //                     ['product_name' => $op['product_name']],
    //                     ['qty' => 0, 'unit' => $op['unit'] ?? 'pcs', 'price' => 0, 'total' => 0]
    //                 );

    //                 $oldProduct->qty += $op['qty'] ?? 0;
    //                 $oldProduct->unit = $op['unit'] ?? $oldProduct->unit;
    //                 $oldProduct->price = $op['price'] ?? $oldProduct->price;
    //                 $oldProduct->total = $oldProduct->qty * $oldProduct->price;
    //                 $oldProduct->save();

    //                 OldProductPurchaseDetail::create([
    //                     'old_product_purchase_id' => $purchase->id,
    //                     'old_product_id'          => $oldProduct->id,
    //                     'qty'                     => $op['qty'] ?? 0,
    //                     'unit'                    => $op['unit'] ?? 'pcs',
    //                     'price'                   => $op['price'] ?? 0,
    //                     'total_amount'            => ($op['qty'] ?? 0) * ($op['price'] ?? 0),
    //                     'narration'               => $op['narration'] ?? null,
    //                 ]);
    //             }
    //         }

    //         $previousReceived = $sale->receivedPayments->sum('amount');
    //         $newPaid = (float) $request->paid_amount;

    //         if ($newPaid != $previousReceived) {
    //             $difference = $newPaid - $previousReceived;
    //             SalesReceived::create([
    //                 'sale_id'         => $sale->id,
    //                 'customer_id'     => $sale->customer_id,
    //                 'received_date'   => $request->sale_date,
    //                 'amount'          => abs($difference),
    //                 'received_method' => $request->payment_method,
    //                 'payment_type'   => $difference > 0 ? 'Adjustment Received' : 'Partial Refund',
    //                 'tracking_no'     => $request->note,
    //             ]);
    //         }

    //         $totalReceived = $sale->refresh()->receivedPayments->sum('amount');
    //         $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);
    //     });

    //     return redirect()->route('sale.index')->with('success', 'Sale Updated Successfully');
    // }

    
public function update(Request $request, $id)
{
    $request->validate([
        'invoice_no'       => 'required|string|max:255|unique:sales,invoice_no,' . $id,
        'sale_date'        => 'required|date',
        'customer_id'      => 'required|exists:customers,id',
        'payment_method'   => 'required|string',
        'paid_amount'      => 'nullable|numeric|min:0',
        'tax_percentage'   => 'nullable|numeric|min:0',
        'shipping_amount'  => 'nullable|numeric|min:0',
        'discount_amount'  => 'nullable|numeric|min:0',
        'body_date'        => 'nullable|date',
        'note'             => 'nullable|string',
        'products'         => 'nullable|array',
        'products.*.product_id' => 'nullable|exists:products,id',
        'products.*.qty'   => 'nullable|numeric|min:0',
        'products.*.price' => 'nullable|numeric|min:0',
        'old_products'     => 'nullable|array',
        'old_products.*.qty'   => 'nullable|numeric|min:0',
        'old_products.*.price' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($request, $id) {

        $sale = Sales::with([
            'details.product',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments',
        ])->lockForUpdate()->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | 1. আগের বিক্রির পণ্যের স্টক ফেরত দিন
        |--------------------------------------------------------------------------
        */

        foreach ($sale->details as $detail) {
            if ($detail->product) {
                $detail->product->increment(
                    'stock',
                    (float) $detail->qty
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. আগের Old Product-এর পরিমাণ ফেরত সমন্বয় করুন
        |--------------------------------------------------------------------------
        */

        foreach ($sale->oldProductPurchases as $purchase) {
            foreach ($purchase->details as $detail) {
                if ($detail->oldProduct) {
                    $oldProduct = $detail->oldProduct;

                    $oldProduct->qty = max(
                        0,
                        (float) $oldProduct->qty - (float) $detail->qty
                    );

                    $oldProduct->total =
                        (float) $oldProduct->qty *
                        (float) $oldProduct->price;

                    $oldProduct->save();
                }
            }

            $purchase->details()->delete();
            $purchase->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. পুরোনো Sale Details মুছে দিন
        |--------------------------------------------------------------------------
        */

        $sale->details()->delete();

        /*
        |--------------------------------------------------------------------------
        | 4. নতুন পণ্যের Subtotal হিসাব
        |--------------------------------------------------------------------------
        */

        $products = $request->input('products', []);
        $oldProducts = $request->input('old_products', []);

        $subtotal = 0;

        foreach ($products as $p) {
            if (empty($p['product_id'])) {
                continue;
            }

            $qty = (float) ($p['qty'] ?? 0);
            $price = (float) ($p['price'] ?? 0);

            $subtotal += $qty * $price;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. পুরোনো ব্যাটারি / পণ্যের মোট মূল্য
        |--------------------------------------------------------------------------
        */

        $oldTotal = 0;

        foreach ($oldProducts as $op) {
            if (empty(trim($op['product_name'] ?? ''))) {
                continue;
            }

            $qty = (float) ($op['qty'] ?? 0);
            $price = (float) ($op['price'] ?? 0);

            $oldTotal += $qty * $price;
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Tax, Shipping, Discount ও Grand Total
        |--------------------------------------------------------------------------
        */

        $taxPercent = (float) $request->input('tax_percentage', 0);
        $taxAmount = round(($subtotal * $taxPercent) / 100, 2);
        $shipping = (float) $request->input('shipping_amount', 0);
        $discount = (float) $request->input('discount_amount', 0);

        $grandTotal = round(max(
            $subtotal + $taxAmount + $shipping - $oldTotal - $discount,
            0
        ), 2);

        /*
        |--------------------------------------------------------------------------
        | 7. Sale আপডেট
        |--------------------------------------------------------------------------
        */

        $sale->update([
            'invoice_no'      => $request->invoice_no,
            'sale_date'       => $request->sale_date,
            'customer_id'     => $request->customer_id,
            'subtotal'        => round($subtotal, 2),
            'tax_percentage'  => $taxPercent,
            'tax_amount'      => $taxAmount,
            'shipping_amount' => $shipping,
            'discount_amount' => $discount,
            'total_amount'    => $grandTotal,
            'note'            => $request->note,
            'body_date'       => $request->body_date,
            'payment_method'  => $request->payment_method,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 8. নতুন Sale Details তৈরি এবং স্টক কমানো
        |--------------------------------------------------------------------------
        */

        foreach ($products as $p) {
            if (empty($p['product_id'])) {
                continue;
            }

            $product = Product::whereKey($p['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $qty = (float) ($p['qty'] ?? 0);
            $price = (float) ($p['price'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            SaleDetail::create([
                'sale_id'    => $sale->id,
                'product_id' => $product->id,
                'qty'        => $qty,
                'unit'       => $p['unit'] ?? 'pcs',
                'price'      => $price,
                'total'      => round($qty * $price, 2),
                'narration'  => $p['narration'] ?? null,
            ]);

            $product->decrement('stock', $qty);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Old Product Purchase তৈরি
        |--------------------------------------------------------------------------
        */

        $validOldProducts = array_filter(
            $oldProducts,
            fn ($op) => !empty(trim($op['product_name'] ?? ''))
        );

        if (count($validOldProducts) > 0) {
            $purchase = OldProductPurchase::create([
                'purchase_no'   => 'OP-' . now()->format('YmdHis') . '-' . $sale->id,
                'sale_id'       => $sale->id,
                'total'         => round($oldTotal, 2),
                'purchase_date' => $request->sale_date,
            ]);

            foreach ($validOldProducts as $op) {
                $name = trim($op['product_name']);
                $qty = (float) ($op['qty'] ?? 0);
                $price = (float) ($op['price'] ?? 0);
                $unit = $op['unit'] ?? 'pcs';

                $oldProduct = OldProduct::firstOrCreate(
                    ['product_name' => $name],
                    [
                        'qty'   => 0,
                        'unit'  => $unit,
                        'price' => 0,
                        'total' => 0,
                    ]
                );

                $oldProduct->qty =
                    (float) $oldProduct->qty + $qty;

                $oldProduct->unit = $unit;
                $oldProduct->price = $price;
                $oldProduct->total =
                    (float) $oldProduct->qty * $price;

                $oldProduct->save();

                OldProductPurchaseDetail::create([
                    'old_product_purchase_id' => $purchase->id,
                    'old_product_id'          => $oldProduct->id,
                    'qty'                     => $qty,
                    'unit'                    => $unit,
                    'price'                   => $price,
                    'total_amount'             => round($qty * $price, 2),
                    'narration'               => $op['narration'] ?? null,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 10. Payment হিসাব ও Adjustment
        |--------------------------------------------------------------------------
        |
        | paid_amount = Edit ফর্মে দেখানো মোট পরিশোধিত টাকা।
        | আগের পেমেন্টের তুলনায় পার্থক্যটুকু নতুন রেকর্ড হবে।
        */

        $previousReceived = round(
            (float) SalesReceived::where('sale_id', $sale->id)
                ->sum('amount'),
            2
        );

        $newPaid = round(
            (float) $request->input('paid_amount', $previousReceived),
            2
        );

        $difference = round($newPaid - $previousReceived, 2);

        if (abs($difference) >= 0.01) {
            SalesReceived::create([
                'sale_id'         => $sale->id,
                'customer_id'     => $sale->customer_id,
                'received_date'   => $request->sale_date,
                'amount'          => $difference,
                'received_method' => $request->payment_method,
                'payment_type'    => $difference > 0
                    ? ($newPaid >= $grandTotal ? 'Full' : 'Partial Payment')
                    : 'Refund Adjustment',
                'tracking_no'     => $request->input('tracking_no'),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 11. সর্বশেষ পেমেন্টের যোগফল দিয়ে Paid / Due আপডেট
        |--------------------------------------------------------------------------
        */

        $totalReceived = round(
            (float) SalesReceived::where('sale_id', $sale->id)
                ->sum('amount'),
            2
        );

        $dueAmount = round($grandTotal - $totalReceived, 2);

        $sale->update([
            'paid_amount'   => $totalReceived,
            'due_amount'    => $dueAmount,
            'received_type' => $totalReceived >= $grandTotal
                ? 'Full'
                : ($totalReceived > 0 ? 'Partial Payment' : 'Unpaid'),
        ]);
    });

    return redirect()
        ->route('sale.index')
        ->with('success', 'Sale Updated Successfully');
}


    // public function view($id)
    // {
    //     $sale = Sales::with([
    //         'customer',
    //         'details.product',
    //         'oldProductPurchases.details.oldProduct',
    //         'receivedPayments',
    //     ])->findOrFail($id);

    //     $company = CompanyInfo::getInfo();
    //     $receivedTotal = (float) $sale->receivedPayments->sum('amount');
    //     $totalPaid = (float) $sale->paid_amount + $receivedTotal;
    //     $dueAmount = (float) $sale->total_amount - $totalPaid;
    //     $previousDue = (float) Sales::where(
    //         'customer_id',
    //         $sale->customer_id
    //     )
    //         ->where('id', '!=', $sale->id)
    //         ->sum('due_amount');

    //     $grandDue = $dueAmount + $previousDue;
    //     return view('sale.view', compact(
    //         'sale',
    //         'company',
    //         'totalPaid',
    //         'dueAmount',
    //         'previousDue',
    //         'grandDue'
    //     ));
    // }

    
public function view($id)
{
    $sale = Sales::with([
        'customer',
        'details.product',
        'oldProductPurchases.details.oldProduct',
        'receivedPayments',
    ])->findOrFail($id);

    $company = CompanyInfo::getInfo();

    // বর্তমান চালানের সঙ্গে সরাসরি যুক্ত পেমেন্ট
    $receivedTotal = (float) $sale->receivedPayments->sum('amount');

    // SalesReceived রেকর্ড থাকলে সেটির হিসাব ব্যবহার হবে।
    // না থাকলে sales.paid_amount ব্যবহার হবে, যাতে একই পেমেন্ট
    // দুবার গণনা না হয়।
    $totalPaid = $sale->receivedPayments->isNotEmpty()
        ? $receivedTotal
        : (float) $sale->paid_amount;

    // বর্তমান চালানের বাকি
    $dueAmount = max(
        0,
        (float) $sale->total_amount - $totalPaid
    );

    // একই ক্রেতার অন্য চালানগুলোর বাকি
    $previousSales = Sales::where('customer_id', $sale->customer_id)
        ->where('id', '!=', $sale->id)
        ->get(['id', 'total_amount', 'paid_amount', 'due_amount']);

    $previousDue = 0;

    foreach ($previousSales as $previousSale) {
        // পূর্বের চালানের পেমেন্ট রেকর্ড আছে কি না যাচাই
        $previousReceived = (float) \App\Models\SalesReceived::where(
            'sale_id',
            $previousSale->id
        )->sum('amount');

        if (
            \App\Models\SalesReceived::where(
                'sale_id',
                $previousSale->id
            )->exists()
        ) {
            $previousPaid = $previousReceived;
        } else {
            $previousPaid = (float) $previousSale->paid_amount;
        }

        $previousSaleDue = max(
            0,
            (float) $previousSale->total_amount - $previousPaid
        );

        $previousDue += $previousSaleDue;
    }

    // ক্রেতার সর্বমোট বাকি
    $grandDue = $previousDue + $dueAmount;

    return view('sale.view', compact(
        'sale',
        'company',
        'totalPaid',
        'dueAmount',
        'previousDue',
        'grandDue'
    ));
}


    public function editPartial($id)
    {
        $sale = Sales::with([
            'customer',
            'details.product',
            'oldProductPurchases.details.oldProduct',
            'receivedPayments'
        ])->findOrFail($id);
        return view('sale.edit_partial', compact('sale'));
    }

    public function savePartialPayment(Request $request, $id)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:1',
            'received_date'   => 'required|date',
            'received_method' => 'required|string',
            'tracking_no'     => 'nullable|string',
        ]);

        $sale = Sales::with('receivedPayments')->findOrFail($id);

        $method = $request->received_method;
        if ($method === 'other') {
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

        $totalReceived = $sale->refresh()->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);
        return redirect()->back()->with('success', 'Partial payment added successfully!');
    }

    public function updatePartialPayment(Request $request, $id)
    {
        $payment = SalesReceived::with('sale')->findOrFail($id);
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
            'received_method_other' => $request->received_method == 'other' ? $request->received_method_other : null,
            'tracking_no' => $request->tracking_no,
        ]);

        $sale = $payment->sale;
        $sale->load('receivedPayments');
        $totalReceived = $sale->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);
        return back()->with('success', 'Partial Payment updated successfully.');
    }

    public function deletePartialPayment($id)
    {
        $payment = SalesReceived::with('sale')->findOrFail($id);
        $sale = $payment->sale;
        $payment->delete();
        $sale->load('receivedPayments');
        $totalReceived = $sale->receivedPayments->sum('amount');
        $sale->update(['due_amount' => $sale->total_amount - $totalReceived]);
        return back()->with('success', 'Partial Payment deleted successfully.');
    }

    public function destroy($id)
    {
        $sale = Sales::with('details.product')->findOrFail($id);
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
        $sales = Sales::with('customer')
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
            $sale = Sales::with('receivedPayments')->findOrFail($id);

            $amount = (float)$request->amount;
            if ($amount == 0) {
                abort(422, 'Amount cannot be zero');
            }

            SalesReceived::create([
                'sale_id'         => $sale->id,
                'customer_id'     => $sale->customer_id,
                'received_date'   => $request->received_date,
                'amount'          => $amount,
                'received_method' => $request->received_method,
                'received_type'   => 'Partial Received',
                'tracking_no'     => $request->tracking_no,
            ]);

            $newTotalReceived = $sale->refresh()->receivedPayments->sum('amount');
            $sale->update(['due_amount' => $sale->total_amount - $newTotalReceived]);
        });

        return redirect()->route('sale.index')->with('success', 'Payment received successfully.');
    }

    //  ============================================================
    //  MAIN PDF: downloadPdf()
    //  ============================================================
public function downloadPdf($id)
{
    $sale = Sales::with([
        'customer',
        'details.product',
        'receivedPayments',
        'oldProductPurchases.details.oldProduct',
    ])->findOrFail($id);

    $companyInfo = DB::table('company_info')->first();
    $printBoth = request()->has('print_both') && request()->get('print_both') == 'true';
    // Previous Due
    $previousDue = 0;
    $previousSales = Sales::where('customer_id', $sale->customer_id)
        ->where('id', '!=', $sale->id)
        ->get();

    foreach ($previousSales as $prev) {
        $received = DB::table('sales_received')->where('sale_id', $prev->id)->sum('amount');
        $discount = $prev->discount_amount ?? 0;
        $net = ($prev->total_amount ?? 0) - $discount;
        $paid = ($prev->paid_amount ?? 0) + $received;
        $previousDue += max(0, $net - $paid);
    }
    $previousDue = (float) $previousDue;

    // mPDF Configuration
    $mpdf = new \Mpdf\Mpdf([
        'mode'             => 'utf-8',
        'format'           => 'A4',
        'autoScriptToLang' => true,
        'autoLangToFont'   => true,
        'fontDir'          => array_merge(
            (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
            [public_path('fonts')]
        ),
        'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
            'solaimanlipi' => [
                'R'          => 'SolaimanLipi.ttf',
                'useOTL'     => 0,
                'useKashida' => 0,
            ]
        ],
        'default_font'  => 'solaimanlipi',
        'margin_top'    => 21,
        'margin_bottom' => 28,
        'margin_left'   => 15,
        'margin_right'  => 15,
        'margin_header' => 5,
        'margin_footer' => 5,
    ]);

    $mpdf->shrink_tables_to_fit   = 0;
    $mpdf->keep_table_proportions = true;
    $mpdf->autoPadding            = false;
    $mpdf->SetAutoPageBreak(true, 22);

    // Company Info
    $companyName    = $companyInfo->company_name_bangla ?? $companyInfo->company_name_english ?? 'কোম্পানির নাম';
    $companyAddress = $companyInfo->company_address_bangla ?? $companyInfo->company_address_english ?? '';
    $companyPhone   = $companyInfo->company_phone_bangla ?? $companyInfo->company_phone_english ?? '';
    $companyEmail   = $companyInfo->email ?? '';

    // Company Logo
    $logoPath = '';
    if (!empty($companyInfo->logo)) {
        $possiblePath = public_path('images/' . $companyInfo->logo);
        if (file_exists($possiblePath)) {
            $logoPath = $possiblePath;
        } else {
            $possiblePath = public_path($companyInfo->logo);
            if (file_exists($possiblePath)) {
                $logoPath = $possiblePath;
            }
        }
    }

    // Header
    $headerHtml = '
        <table style="width:100%; border-collapse:collapse; border:none; font-family:solaimanlipi; border-bottom:2px solid #000; padding-bottom:4px;">
            <tr>
                <td style="border:none; width:15%; text-align:left; vertical-align:middle; padding:0 6px 0 0;">';
                if ($logoPath) {
                    $headerHtml .= '<img src="file://' . $logoPath . '" style="height: 35px;; max-width:100%;">';
                }
                $headerHtml .= '
                </td>
                <td style="border:none; width:85%; text-align:center; vertical-align:middle; padding:0;">
                    <h2 style="margin:0; font-size:19px; line-height:1.3; font-weight:bold;">' . e($companyName) . '</h2>
                    <p style="margin:2px 0; font-size:11px; line-height:1.4;">
                        ' . e($companyAddress) . '<br>
                        মোবাইল: ' . e($companyPhone) . ($companyEmail ? ' | ইমেইল: ' . e($companyEmail) : '') . '
                    </p>
                </td>
            </tr>
        </table>
    ';
    $mpdf->SetHTMLHeader($headerHtml);

    // Footer
    $footerHtml = '
        <div style="text-align:center; border-top:1px solid #000; padding-top:4px; font-size:11px; font-family:solaimanlipi; line-height:1.5;">
            সফটওয়্যার দ্বারা জেনারেট করা রিপোর্ট | ' . e($companyName) . ' | মোবাইল: ' . e($companyPhone) . '
            <br>পৃষ্ঠা {PAGENO} / {nbpg}
        </div>
    ';
    $mpdf->SetHTMLFooter($footerHtml);
    $mpdf->WriteHTML('
        <style>
            * {
                font-family: solaimanlipi, sans-serif !important;
                line-height: 1.12;
            }
            body {
                font-family: solaimanlipi, sans-serif !important;
                font-size: 14px;
                margin: 0;
                padding: 0;
                line-height: 1.12;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                border: 1px solid #999;
                padding: 2px 5px;
                font-size: 14px;
                background-color: #f0f0f0;
                font-weight: bold;
                line-height: 1.15;
            }
            td {
                border: 1px solid #999;
                padding: 2px 5px;
                font-size: 14px;
                line-height: 1.15;
            }
            h3 {
                font-size: 18px;
                margin: 4px 0 !important;
                line-height: 1.2;
                font-weight: bold;
            }
            h4 {
                font-size: 15px;
                margin: 3px 0 !important;
                line-height: 1.2;
                font-weight: bold;
            }
            p {
                margin: 1px 0 !important;
                font-size: 14px;
                line-height: 1.25;
            }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        </style>
    ');

    // Challan Copy
    if ($printBoth) {
        $challanHtml = view('sale.pdf_challan_chunked', [
            'sale'          => $sale,
            'companyInfo'   => $companyInfo,
            'printBoth'     => $printBoth,
            'chunkSize'     => 38,
        ])->render();

        $challanHtml = rtrim($challanHtml);
        $mpdf->WriteHTML($challanHtml);
        $mpdf->WriteHTML('<pagebreak />');
    }

    // Bill Copy
    $billHtml = view('sale.pdf_bill_chunked', [
        'sale'          => $sale,
        'companyInfo'   => $companyInfo,
        'printBoth'     => $printBoth,
        'chunkSize'     => 38,
    ])->render();

    $billHtml = rtrim($billHtml);
    $mpdf->WriteHTML($billHtml);
    $invoice = $sale->invoice_no ?? $sale->id;
    $invoice = preg_replace('/[^A-Za-z0-9\-]/', '_', $invoice);
    return $mpdf->output('Sale_' . $invoice . '.pdf', 'D');
}

//  ============================================================
//  PARTIAL PAYMENT PDF
//  ============================================================

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
        // $previousDue += max(0, $prevNet - $prevPaid);
        $previousDue += ($prevNet - $prevPaid);
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
            $net = ($s->total_amount ?? 0) - $discount;
            $paid = ($s->paid_amount ?? 0) + $received;
            return [
                'invoice' => $s->invoice_no,
                'date'    => $s->sale_date, // Carbon instance
                'total'   => $net,
                'paid'    => $paid,
                'due'     => ($net - $paid),
                'is_current' => $s->id == $sale->id
            ];
        });

    // ⭐ Calculating totals for last sales data
    $totalSaleAmount = $lastSalesData->sum('total');
    $totalSalePaid   = $lastSalesData->sum('paid');
    $totalSaleDue    = $lastSalesData->sum('due');

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
        'totalSaleAmount' => $totalSaleAmount,
        'totalSalePaid'   => $totalSalePaid,
        'totalSaleDue'    => $totalSaleDue,
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