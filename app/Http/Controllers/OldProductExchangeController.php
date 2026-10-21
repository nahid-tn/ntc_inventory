<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OldProduct;
use App\Models\OldProductExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OldProductExchangeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = OldProductExchange::with('customer');

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('exchange_no', 'like', "%{$search}%")
                    ->orWhere('invoice_no', 'like', "%{$search}%")
                    ->orWhere('old_product_name', 'like', "%{$search}%")
                    ->orWhere('exchange_product_name', 'like', "%{$search}%")

                    /*
                    |--------------------------------------------------------------------------
                    | Customer Search
                    |--------------------------------------------------------------------------
                    */

                    ->orWhereHas('customer', function ($customerQuery) use ($search) {

                        $customerQuery
                            ->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");

                    });

            });
        }

        if ($request->filled('from_date')) {

            $query->whereDate(
                'exchange_date',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {

            $query->whereDate(
                'exchange_date',
                '<=',
                $request->to_date
            );
        }

        $exchanges = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'old_product_exchange.index',
            compact('exchanges')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $customers = Customer::where('status', 'active')
            ->orderBy('customer_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Old Products
        |--------------------------------------------------------------------------
        |
        | Stock > 0 filter দেওয়া হচ্ছে না।
        | কারণ Customer যে product দেয় সেটি stock 0 হলেও
        | Customer-এর product হিসেবে নেওয়া যাবে।
        |
        */

        $oldProducts = OldProduct::orderBy('id', 'asc')
        ->get();

        return view(
            'old_product_exchange.create',
            compact(
                'oldProducts',
                'customers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT AJAX
    |--------------------------------------------------------------------------
    */

    public function product($id)
    {
        $product = OldProduct::findOrFail($id);

        return response()->json([
            'id'           => $product->id,
            'product_name' => $product->product_name,
            'qty'          => (float) $product->qty,
            'stock'        => (float) $product->stock,
            'unit'         => $product->unit,
            'price'        => (float) $product->price,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'invoice_no' => ['nullable','string','max:100'],
            'customer_id' => [ 'required', 'integer', 'exists:customers,id' ],

            /*
            |--------------------------------------------------------------------------
            | Customer Gives
            |--------------------------------------------------------------------------
            */

            'old_product_id' => [
                'required',
                'integer',
                'exists:old_products,id'
            ],

            'old_qty' => [
                Rule::requiredIf(
                    !in_array((int) $request->input('old_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'old_weight_kg' => [
                Rule::requiredIf(
                    in_array((int) $request->input('old_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer Takes
            |--------------------------------------------------------------------------
            */

            'exchange_product_id' => [
                'required',
                'integer',
                'exists:old_products,id'
            ],

            'exchange_qty' => [
                Rule::requiredIf(
                    !in_array((int) $request->input('exchange_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'exchange_weight_kg' => [
                Rule::requiredIf(
                    in_array((int) $request->input('exchange_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'weight_rate' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'exchange_fee' => [
                'required',
                'numeric',
                'min:0'
            ],

            'exchange_date' => [
                'required',
                'date'
            ],

            'notes' => [
                'nullable',
                'string'
            ],
        ]);


        // Normalize disabled/missing fields to zero; the selected product type
        // determines whether quantity or weight is actually used.
        $oldQty = (float) ($validated['old_qty'] ?? 0);
        $exchangeQty = (float) ($validated['exchange_qty'] ?? 0);
        $oldWeight = (float) ($validated['old_weight_kg'] ?? 0);
        $exchangeWeight = (float) ($validated['exchange_weight_kg'] ?? 0);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Validate Customer
            |--------------------------------------------------------------------------
            */

            $customer = Customer::where('id', $validated['customer_id'])
                ->where('status', 'active')
                ->first();

            if (!$customer) {
                throw new \Exception(
                    'নির্বাচিত Customer active নেই।'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Lock Product Rows
            |--------------------------------------------------------------------------
            */

            $oldProduct = OldProduct::where(
                'id',
                $validated['old_product_id']
            )
                ->lockForUpdate()
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Same product হলে একই row দুইবার lock না করে
            | একই object ব্যবহার
            |--------------------------------------------------------------------------
            */

            if (
                $validated['old_product_id'] == $validated['exchange_product_id']
            ) {

                $exchangeProduct = $oldProduct;

            } else {

                $exchangeProduct = OldProduct::where(
                    'id',
                    $validated['exchange_product_id']
                )
                    ->lockForUpdate()
                    ->firstOrFail();
            }


            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */



            /*
            |--------------------------------------------------------------------------
            | SAME PRODUCT STOCK CHECK
            |--------------------------------------------------------------------------
            */

            if (
                $oldProduct->id == $exchangeProduct->id
            ) {
                $incomingStock = $this->productStockMovement(
                    (int) $oldProduct->id,
                    $oldQty,
                    $oldWeight
                );

                $outgoingStock = $this->productStockMovement(
                    (int) $exchangeProduct->id,
                    $exchangeQty,
                    $exchangeWeight
                );

                $availableAfterIncoming =
                    (float) $exchangeProduct->stock + $incomingStock;

                if ($availableAfterIncoming < $outgoingStock) {

                    throw new \Exception(
                        'Exchange করার জন্য পর্যাপ্ত stock নেই।'
                    );
                }

            } else {

                $outgoingStock = $this->productStockMovement(
                    (int) $exchangeProduct->id,
                    $exchangeQty,
                    $exchangeWeight
                );

                if (
                    (float) $exchangeProduct->stock < $outgoingStock
                ) {

                    throw new \Exception(
                        'Customer যে product নিতে চাচ্ছেন তার পর্যাপ্ত stock নেই।'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Snapshot Data
            |--------------------------------------------------------------------------
            */

            $oldPrice = (float) $oldProduct->price;
            $exchangePrice = (float) $exchangeProduct->price;

            $this->validateProductInput(
                (int) $oldProduct->id,
                $oldQty,
                $oldWeight,
                'Customer Gives Product'
            );

            $this->validateProductInput(
                (int) $exchangeProduct->id,
                $exchangeQty,
                $exchangeWeight,
                'Customer Takes Product'
            );

            /*
             * ID 1/2 => Weight × Price
             * ID 3   => Quantity × Price
             * Other  => Quantity × Price
             */
            $oldTotal = $this->productBaseValue(
                (int) $oldProduct->id,
                $oldQty,
                $oldWeight,
                $oldPrice
            );

            /*
             * Exchange product value also follows its own product type.
             * This keeps weight-priced products consistent.
             */
            $exchangeTotal = $this->productBaseValue(
                (int) $exchangeProduct->id,
                $exchangeQty,
                $exchangeWeight,
                $exchangePrice
            );


            /*
            |--------------------------------------------------------------------------
            | Weight Calculation
            |--------------------------------------------------------------------------
            */

            $weightRate = (float) ($validated['weight_rate'] ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Weight Difference
            |--------------------------------------------------------------------------
            |
            | Positive = customer receives extra weight
            | Negative = customer gives extra weight
            |
            */

            $weightDifference =
                $exchangeWeight - $oldWeight;

            /*
            |--------------------------------------------------------------------------
            | Weight Adjustment
            |--------------------------------------------------------------------------
            */

            $weightAdjustment = $weightDifference * $weightRate;

            /*
            |--------------------------------------------------------------------------
            | Product Value Difference
            |--------------------------------------------------------------------------
            */

            $valueDifference = $exchangeTotal - $oldTotal;

            /*
            |--------------------------------------------------------------------------
            | Final Settlement
            |--------------------------------------------------------------------------
            |
            | Customer takes more valuable product:
            | customer pays
            |
            | Customer gives more valuable product:
            | customer receives
            |
            */

            $settlementAmount =
                $valueDifference
                + $weightAdjustment
                + (float) $validated['exchange_fee'];


            if ($settlementAmount > 0) {

                $settlementType =
                    'customer_pay';

            } elseif ($settlementAmount < 0) {

                $settlementType =
                    'customer_receive';

            } else {

                $settlementType =
                    'no_adjustment';
            }


            /*
            |--------------------------------------------------------------------------
            | STOCK UPDATE
            |--------------------------------------------------------------------------
            */

            if (
                $oldProduct->id == $exchangeProduct->id
            ) {

                /*
                | Same product:
                |
                | stock = stock + incoming - outgoing
                */

                $incomingStock = $this->productStockMovement(
                    (int) $oldProduct->id,
                    $oldQty,
                    $oldWeight
                );

                $outgoingStock = $this->productStockMovement(
                    (int) $exchangeProduct->id,
                    $exchangeQty,
                    $exchangeWeight
                );

                $newStock =
                    (float) $oldProduct->stock
                    + $incomingStock
                    - $outgoingStock;

                $oldProduct->stock =
                    $newStock;

                $oldProduct->save();

            } else {

                /*
                |--------------------------------------------------------------------------
                | Customer Gives Product
                |--------------------------------------------------------------------------
                */

                $oldProduct->stock =
                    (float) $oldProduct->stock
                    + $this->productStockMovement(
                        (int) $oldProduct->id,
                        $oldQty,
                        $oldWeight
                    );

                $oldProduct->save();


                /*
                |--------------------------------------------------------------------------
                | Customer Takes Product
                |--------------------------------------------------------------------------
                */

                $exchangeProduct->stock =
                    (float) $exchangeProduct->stock
                    - $this->productStockMovement(
                        (int) $exchangeProduct->id,
                        $exchangeQty,
                        $exchangeWeight
                    );

                $exchangeProduct->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Create Exchange
            |--------------------------------------------------------------------------
            */

            $exchange = OldProductExchange::create([

                'exchange_no' =>
                    'TEMP-' . Str::uuid(),

                'invoice_no' =>
                    $validated['invoice_no'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                'customer_id' =>
                    $validated['customer_id'],


                /*
                |--------------------------------------------------------------------------
                | Customer Gives
                |--------------------------------------------------------------------------
                */

                'old_product_id' => $oldProduct->id,
                'old_product_name' => $oldProduct->product_name,
                'old_qty' => $oldQty,
                'old_unit' => $oldProduct->unit,
                'old_weight_kg' => $oldWeight,
                'old_price' => $oldPrice,
                'old_total' => $oldTotal,


                /*
                |--------------------------------------------------------------------------
                | Customer Takes
                |--------------------------------------------------------------------------
                */

                'exchange_product_id' => $exchangeProduct->id,
                'exchange_product_name' => $exchangeProduct->product_name,
                'exchange_qty' => $exchangeQty,
                'exchange_unit' => $exchangeProduct->unit,
                'exchange_weight_kg' => $exchangeWeight,
                'exchange_price' => $exchangePrice,
                'exchange_total' => $exchangeTotal,


                /*
                |--------------------------------------------------------------------------
                | Weight
                |--------------------------------------------------------------------------
                */

                'weight_difference_kg' => $weightDifference,
                'weight_rate' => $weightRate,
                'weight_adjustment' => $weightAdjustment,


                /*
                |--------------------------------------------------------------------------
                | Fee
                |--------------------------------------------------------------------------
                */

                'exchange_fee' => (float) $validated['exchange_fee'],

                /*
                |--------------------------------------------------------------------------
                | Settlement
                |--------------------------------------------------------------------------
                */

                'value_difference' => $valueDifference,
                'settlement_amount' => abs($settlementAmount),
                'settlement_type' => $settlementType,


                /*
                |--------------------------------------------------------------------------
                | Date / Status
                |--------------------------------------------------------------------------
                */

                'exchange_date' => $validated['exchange_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Final Exchange Number
            |--------------------------------------------------------------------------
            */

            $exchangeNo =
                'EXC-'
                . Carbon::parse(
                    $validated['exchange_date']
                )->format('Ymd')
                . '-'
                . str_pad(
                    $exchange->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            $exchange->exchange_no = $exchangeNo;
            $exchange->save();

            DB::commit();

            return redirect()
                ->route(
                    'old_product_exchange.show',
                    $exchange->id
                )
                ->with(
                    'success',
                    'Old Product Exchange সফলভাবে সম্পন্ন হয়েছে।'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /* ----------------------------| SHOW */

public function show($id)
{
    $exchange = OldProductExchange::with('customer')
        ->findOrFail($id);

    return view(
        'old_product_exchange.show',
        compact('exchange')
    );
}

    /*------------------------------ | EDIT ------------------------*/

public function edit($id)
{
    $exchange = OldProductExchange::with([
        'customer',
    ])->findOrFail($id);

    if ($exchange->status === 'cancelled') {
        return redirect()
            ->route('old_product_exchange.show', $exchange->id)
            ->with('error', 'Cancelled exchange edit করা যাবে না।');
    }

    $oldProducts = OldProduct::select([
        'id',
        'product_name',
        'stock',
        'unit',
        'price',
    ])
    ->orderBy('product_name')
    ->get();

    return view(
        'old_product_exchange.edit',
        compact('exchange', 'oldProducts')
    );
}

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        $exchange =
            OldProductExchange::findOrFail($id);

        if ($exchange->status === 'cancelled') {

            return back()
                ->with(
                    'error',
                    'Cancelled Exchange update করা যাবে না।'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'invoice_no' => [
                'nullable',
                'string',
                'max:100'
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id'
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer Gives
            |--------------------------------------------------------------------------
            */

            'old_product_id' => [
                'required',
                'integer',
                'exists:old_products,id'
            ],

            'old_qty' => [
                Rule::requiredIf(
                    !in_array((int) $request->input('old_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'old_weight_kg' => [
                Rule::requiredIf(
                    in_array((int) $request->input('old_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer Takes
            |--------------------------------------------------------------------------
            */

            'exchange_product_id' => [
                'required',
                'integer',
                'exists:old_products,id'
            ],

            'exchange_qty' => [
                Rule::requiredIf(
                    !in_array((int) $request->input('exchange_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'exchange_weight_kg' => [
                Rule::requiredIf(
                    in_array((int) $request->input('exchange_product_id'), [1, 2], true)
                ),
                'nullable',
                'numeric',
                'min:0',
            ],

            'weight_rate' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'exchange_fee' => [
                'required',
                'numeric',
                'min:0'
            ],

            'exchange_date' => [
                'required',
                'date'
            ],

            'notes' => [
                'nullable',
                'string'
            ],
        ]);


        // Normalize disabled/missing fields to zero; the selected product type
        // determines whether quantity or weight is actually used.
        $oldQty = (float) ($validated['old_qty'] ?? 0);
        $exchangeQty = (float) ($validated['exchange_qty'] ?? 0);
        $oldWeight = (float) ($validated['old_weight_kg'] ?? 0);
        $exchangeWeight = (float) ($validated['exchange_weight_kg'] ?? 0);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Validate Customer
            |--------------------------------------------------------------------------
            */

            $customer = Customer::where(
                'id',
                $validated['customer_id']
            )
                ->where('status', 'active')
                ->first();

            if (!$customer) {

                throw new \Exception(
                    'নির্বাচিত Customer active নেই।'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Reverse OLD Transaction
            |--------------------------------------------------------------------------
            */

            $oldGivenProduct =
                OldProduct::where(
                    'id',
                    $exchange->old_product_id
                )
                    ->lockForUpdate()
                    ->firstOrFail();


            $oldTakenProduct =
                OldProduct::where(
                    'id',
                    $exchange->exchange_product_id
                )
                    ->lockForUpdate()
                    ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Reverse Customer Gave
            |--------------------------------------------------------------------------
            |
            | আগে + হয়েছিল
            | এখন - হবে
            |
            */

            if (
                $oldGivenProduct->id
                ==
                $oldTakenProduct->id
            ) {

                /*
                | Same product হলে net movement reverse
                */

                $oldGivenProduct->stock =
                    (float) $oldGivenProduct->stock
                    - $this->productStockMovement(
                        (int) $exchange->old_product_id,
                        (float) $exchange->old_qty,
                        (float) $exchange->old_weight_kg
                    )
                    + $this->productStockMovement(
                        (int) $exchange->exchange_product_id,
                        (float) $exchange->exchange_qty,
                        (float) $exchange->exchange_weight_kg
                    );

                $oldGivenProduct->save();

            } else {

                /*
                | Customer আগে যে product দিয়েছিল
                */

                $oldGivenProduct->stock =
                    (float) $oldGivenProduct->stock
                    - $this->productStockMovement(
                        (int) $exchange->old_product_id,
                        (float) $exchange->old_qty,
                        (float) $exchange->old_weight_kg
                    );

                $oldGivenProduct->save();


                /*
                | Customer আগে যে product নিয়েছিল
                | সেটা আবার stock-এ ফেরত
                */

                $oldTakenProduct->stock =
                    (float) $oldTakenProduct->stock
                    + $this->productStockMovement(
                        (int) $exchange->exchange_product_id,
                        (float) $exchange->exchange_qty,
                        (float) $exchange->exchange_weight_kg
                    );

                $oldTakenProduct->save();
            }


            /*
            |--------------------------------------------------------------------------
            | New Products Lock
            |--------------------------------------------------------------------------
            */

            $newOldProduct =
                OldProduct::where(
                    'id',
                    $validated['old_product_id']
                )
                    ->lockForUpdate()
                    ->firstOrFail();


            if (
                $validated['old_product_id']
                ==
                $validated['exchange_product_id']
            ) {

                $newExchangeProduct =
                    $newOldProduct;

            } else {

                $newExchangeProduct =
                    OldProduct::where(
                        'id',
                        $validated['exchange_product_id']
                    )
                        ->lockForUpdate()
                        ->firstOrFail();
            }


            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */



            /*
            |--------------------------------------------------------------------------
            | Stock Check
            |--------------------------------------------------------------------------
            */

            if (
                $newOldProduct->id
                ==
                $newExchangeProduct->id
            ) {

                $incomingStock = $this->productStockMovement(
                    (int) $newOldProduct->id,
                    $oldQty,
                    $oldWeight
                );

                $outgoingStock = $this->productStockMovement(
                    (int) $newExchangeProduct->id,
                    $exchangeQty,
                    $exchangeWeight
                );

                $available =
                    (float) $newExchangeProduct->stock
                    + $incomingStock;

                if ($available < $outgoingStock) {

                    throw new \Exception(
                        'Exchange product-এর পর্যাপ্ত stock নেই।'
                    );
                }

            } else {

                $outgoingStock = $this->productStockMovement(
                    (int) $newExchangeProduct->id,
                    $exchangeQty,
                    $exchangeWeight
                );

                if (
                    (float) $newExchangeProduct->stock
                    <
                    $outgoingStock
                ) {

                    throw new \Exception(
                        'Exchange product-এর পর্যাপ্ত stock নেই।'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Calculate
            |--------------------------------------------------------------------------
            */

            $oldPrice =
                (float) $newOldProduct->price;

            $exchangePrice =
                (float) $newExchangeProduct->price;

            $this->validateProductInput(
                (int) $newOldProduct->id,
                $oldQty,
                $oldWeight,
                'Customer Gives Product'
            );

            $this->validateProductInput(
                (int) $newExchangeProduct->id,
                $exchangeQty,
                $exchangeWeight,
                'Customer Takes Product'
            );

            $oldTotal = $this->productBaseValue(
                (int) $newOldProduct->id,
                $oldQty,
                $oldWeight,
                $oldPrice
            );

            $exchangeTotal = $this->productBaseValue(
                (int) $newExchangeProduct->id,
                $exchangeQty,
                $exchangeWeight,
                $exchangePrice
            );

            $weightRate =
                (float) ($validated['weight_rate'] ?? 0);


            $weightDifference =
                $exchangeWeight - $oldWeight;


            $weightAdjustment =
                $weightDifference * $weightRate;


            $valueDifference =
                $exchangeTotal - $oldTotal;


            $settlement =
                $valueDifference
                + $weightAdjustment
                + (float) $validated['exchange_fee'];


            if ($settlement > 0) {

                $settlementType =
                    'customer_pay';

            } elseif ($settlement < 0) {

                $settlementType =
                    'customer_receive';

            } else {

                $settlementType =
                    'no_adjustment';
            }


            /*
            |--------------------------------------------------------------------------
            | Apply New Stock
            |--------------------------------------------------------------------------
            */

            if (
                $newOldProduct->id
                ==
                $newExchangeProduct->id
            ) {

                $newOldProduct->stock =
                    (float) $newOldProduct->stock
                    + $this->productStockMovement(
                        (int) $newOldProduct->id,
                        $oldQty,
                        $oldWeight
                    )
                    - $this->productStockMovement(
                        (int) $newExchangeProduct->id,
                        $exchangeQty,
                        $exchangeWeight
                    );

                $newOldProduct->save();

            } else {

                $newOldProduct->stock =
                    (float) $newOldProduct->stock
                    + $this->productStockMovement(
                        (int) $newOldProduct->id,
                        $oldQty,
                        $oldWeight
                    );

                $newOldProduct->save();


                $newExchangeProduct->stock =
                    (float) $newExchangeProduct->stock
                    - $this->productStockMovement(
                        (int) $newExchangeProduct->id,
                        $exchangeQty,
                        $exchangeWeight
                    );

                $newExchangeProduct->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Update Exchange
            |--------------------------------------------------------------------------
            */

            $exchange->update([

                'invoice_no' =>
                    $validated['invoice_no'] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                'customer_id' =>
                    $validated['customer_id'],


                /*
                |--------------------------------------------------------------------------
                | Customer Gives
                |--------------------------------------------------------------------------
                */

                'old_product_id' =>
                    $newOldProduct->id,

                'old_product_name' =>
                    $newOldProduct->product_name,

                'old_qty' =>
                    $oldQty,

                'old_unit' =>
                    $newOldProduct->unit,

                'old_weight_kg' =>
                    $oldWeight,

                'old_price' =>
                    $oldPrice,

                'old_total' =>
                    $oldTotal,


                /*
                |--------------------------------------------------------------------------
                | Customer Takes
                |--------------------------------------------------------------------------
                */

                'exchange_product_id' =>
                    $newExchangeProduct->id,

                'exchange_product_name' =>
                    $newExchangeProduct->product_name,

                'exchange_qty' =>
                    $exchangeQty,

                'exchange_unit' =>
                    $newExchangeProduct->unit,

                'exchange_weight_kg' =>
                    $exchangeWeight,

                'exchange_price' =>
                    $exchangePrice,

                'exchange_total' =>
                    $exchangeTotal,


                /*
                |--------------------------------------------------------------------------
                | Weight
                |--------------------------------------------------------------------------
                */

                'weight_difference_kg' =>
                    $weightDifference,

                'weight_rate' =>
                    $weightRate,

                'weight_adjustment' =>
                    $weightAdjustment,


                /*
                |--------------------------------------------------------------------------
                | Fee
                |--------------------------------------------------------------------------
                */

                'exchange_fee' =>
                    (float) $validated['exchange_fee'],


                /*
                |--------------------------------------------------------------------------
                | Settlement
                |--------------------------------------------------------------------------
                */

                'value_difference' =>
                    $valueDifference,

                'settlement_amount' =>
                    abs($settlement),

                'settlement_type' =>
                    $settlementType,


                /*
                |--------------------------------------------------------------------------
                | Date / Notes
                |--------------------------------------------------------------------------
                */

                'exchange_date' =>
                    $validated['exchange_date'],

                'notes' =>
                    $validated['notes'] ?? null,
            ]);


            DB::commit();


            return redirect()
                ->route(
                    'old_product_exchange.show',
                    $exchange->id
                )
                ->with(
                    'success',
                    'Exchange সফলভাবে Update হয়েছে।'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        $id
    ) {

        $exchange =
            OldProductExchange::findOrFail($id);

        if ($exchange->status === 'cancelled') {

            return back()
                ->with(
                    'error',
                    'এই Exchange ইতিমধ্যে Cancelled।'
                );
        }


        $request->validate([
            'cancel_reason' => [
                'required',
                'string',
                'max:1000'
            ]
        ]);


        DB::beginTransaction();

        try {

            $oldProduct =
                OldProduct::where(
                    'id',
                    $exchange->old_product_id
                )
                    ->lockForUpdate()
                    ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Same Product
            |--------------------------------------------------------------------------
            */

            if (
                $exchange->old_product_id
                ==
                $exchange->exchange_product_id
            ) {

                $oldProduct->stock =
                    (float) $oldProduct->stock
                    - $this->productStockMovement(
                        (int) $exchange->old_product_id,
                        (float) $exchange->old_qty,
                        (float) $exchange->old_weight_kg
                    )
                    + $this->productStockMovement(
                        (int) $exchange->exchange_product_id,
                        (float) $exchange->exchange_qty,
                        (float) $exchange->exchange_weight_kg
                    );

                $oldProduct->save();

            } else {

                /*
                |--------------------------------------------------------------------------
                | Customer-এর দেওয়া product reverse
                |--------------------------------------------------------------------------
                */

                $oldProduct->stock =
                    (float) $oldProduct->stock
                    - $this->productStockMovement(
                        (int) $exchange->old_product_id,
                        (float) $exchange->old_qty,
                        (float) $exchange->old_weight_kg
                    );

                $oldProduct->save();


                /*
                |--------------------------------------------------------------------------
                | Customer যে product নিয়েছিল
                | সেটা stock-এ ফেরত
                |--------------------------------------------------------------------------
                */

                $exchangeProduct =
                    OldProduct::where(
                        'id',
                        $exchange->exchange_product_id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();


                $exchangeProduct->stock =
                    (float) $exchangeProduct->stock
                    + $this->productStockMovement(
                        (int) $exchange->exchange_product_id,
                        (float) $exchange->exchange_qty,
                        (float) $exchange->exchange_weight_kg
                    );

                $exchangeProduct->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Cancel Exchange
            |--------------------------------------------------------------------------
            */

            $exchange->status =
                'cancelled';

            $exchange->cancelled_at =
                now();

            $exchange->cancelled_by =
                auth()->id();

            $exchange->cancel_reason =
                $request->cancel_reason;

            $exchange->save();


            DB::commit();


            return redirect()
                ->route(
                    'old_product_exchange.index'
                )
                ->with(
                    'success',
                    'Exchange Cancelled এবং Stock Reverse হয়েছে।'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $exchange =
            OldProductExchange::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Completed transaction সরাসরি delete করা যাবে না।
        |--------------------------------------------------------------------------
        */

        if ($exchange->status === 'completed') {

            return back()
                ->with(
                    'error',
                    'Completed Exchange সরাসরি Delete করা যাবে না। আগে Cancel করুন।'
                );
        }


        $exchange->delete();

        return redirect()
            ->route(
                'old_product_exchange.index'
            )
            ->with(
                'success',
                'Exchange deleted হয়েছে।'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | CALCULATION HELPERS
    |--------------------------------------------------------------------------
    | Product ID 1 = Powder Battery      => Weight based
    | Product ID 2 = Water Old Battery   => Weight based
    | Product ID 3 = Borak Old Battery   => Quantity based
    | Other products                    => Quantity based
    |--------------------------------------------------------------------------
    */

    private function calculationType(int $productId): string
    {
        return in_array($productId, [1, 2], true)
            ? 'weight'
            : 'qty';
    }

    private function productBaseValue(
        int $productId,
        float $qty,
        float $weight,
        float $price
    ): float {
        return $this->calculationType($productId) === 'weight'
            ? $weight * $price
            : $qty * $price;
    }

    private function productStockMovement(
        int $productId,
        float $qty,
        float $weight
    ): float {
        return $this->calculationType($productId) === 'weight'
            ? $weight
            : $qty;
    }

    private function validateProductInput(
        int $productId,
        float $qty,
        float $weight,
        string $prefix = 'Product'
    ): void {
        if ($this->calculationType($productId) === 'weight') {
            if ($weight <= 0) {
                throw new \Exception(
                    "{$prefix}-এর Weight (KG) 0-এর বেশি হতে হবে।"
                );
            }
        } else {
            if ($qty <= 0) {
                throw new \Exception(
                    "{$prefix}-এর Quantity 0-এর বেশি হতে হবে।"
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INVOICE
    |--------------------------------------------------------------------------
    */

    public function invoice($id)
    {
        $exchange =
            OldProductExchange::with([
                'customer'
            ])->findOrFail($id);

        return view(
            'old_product_exchange.invoice',
            compact('exchange')
        );
    }
}