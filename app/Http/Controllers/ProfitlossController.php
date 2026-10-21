<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class ProfitlossController extends Controller
{
    public function index()
    {
        $reports = DB::table('products as p')
            /*

            Purchase (Minus Return)

            */
            ->leftJoin(DB::raw('(
                SELECT 
                    pd.product_id,
                    SUM(pd.qty) - COALESCE(SUM(pri.qty),0) AS total_qty,
                    SUM(pd.qty * pd.price) - COALESCE(SUM(pri.qty * pri.price),0) AS total_amount
                FROM purchase_details pd
                LEFT JOIN purchase_return_items pri
                    ON pd.product_id = pri.product_id
                GROUP BY pd.product_id
            ) as purchase_data'), 'p.id', '=', 'purchase_data.product_id')

            /*

            Sale

            */
            ->leftJoin(DB::raw('(
                SELECT 
                    product_id,
                    SUM(qty) as total_qty,
                    SUM(qty * price) as total_amount
                FROM sale_details
                GROUP BY product_id
            ) as sd'), 'p.id', '=', 'sd.product_id')

            ->select(
                'p.id',
                'p.product_name',
                DB::raw('COALESCE(purchase_data.total_qty,0) as purchase_qty'),
                DB::raw('COALESCE(purchase_data.total_amount,0) as purchase_amount'),
                DB::raw('COALESCE(sd.total_qty,0) as sale_qty'),
                DB::raw('COALESCE(sd.total_amount,0) as sale_amount'),
                DB::raw('COALESCE(sd.total_amount,0) - COALESCE(purchase_data.total_amount,0) as profit_loss')
            )
            ->orderBy('p.product_name')
            ->get();

        /*

        Overall Summary

        */
        $total_purchase = DB::table('purchase_details as pd')
            ->leftJoin('purchase_return_items as pri', 'pd.product_id', '=', 'pri.product_id')
            ->select(DB::raw('
                SUM(pd.qty * pd.price) - COALESCE(SUM(pri.qty * pri.price),0) as total
            '))
            ->value('total');

        $total_sale = DB::table('sale_details')
            ->select(DB::raw('SUM(qty * price) as total'))
            ->value('total');

        $net_profit = ($total_sale ?? 0) - ($total_purchase ?? 0);

        return view('ProfitLos.profitlos', compact(
            'reports',
            'total_purchase',
            'total_sale',
            'net_profit'
        ));
    }

    
    public function profitLossReport(Request $request)
    {
        $startDate = Carbon::parse(
            $request->start_date ?? now()->startOfMonth()
        )->startOfDay();

        $endDate = Carbon::parse(
            $request->end_date ?? now()->endOfMonth()
        )->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | NEW PRODUCT SALES
        |--------------------------------------------------------------------------
        */
        $newProductsRaw = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->join('products as p', 'p.id', '=', 'sd.product_id')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->select(
                'sd.product_id',
                'p.product_name',
                'sd.qty',
                'sd.price as sale_price',
                'sd.total as sale_total',
                's.sale_date',

                DB::raw("
                (
                    SELECT

                        pd.price

                        +

                        (
                            IFNULL(pu.shipping_amount,0)

                            *

                            ((pd.qty * pd.price) /

                            NULLIF(
                                (
                                    SELECT SUM(pd2.qty * pd2.price)
                                    FROM purchase_details pd2
                                    WHERE pd2.purchase_id = pu.id
                                ),
                                0
                            ))

                        )

                        /

                        NULLIF(pd.qty,0)

                    FROM purchase_details pd
                    INNER JOIN purchases pu
                        ON pu.id = pd.purchase_id

                    WHERE pd.product_id = sd.product_id
                    AND pu.date <= s.sale_date

                    ORDER BY pu.date DESC,
                            pd.id DESC

                    LIMIT 1

                ) as actual_cost_price
                ")
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GROUP PRODUCT
        |--------------------------------------------------------------------------
        */
        $newProducts = $newProductsRaw
            ->groupBy('product_id')
            ->map(function ($items) {

                $first = $items->first();

                $totalQty = $items->sum('qty');

                $totalSale = $items->sum('sale_total');

                $totalCost = $items->sum(function ($row) {

                    return $row->qty *
                        ($row->actual_cost_price ?? 0);

                });

                return (object) [

                    'product_name' => $first->product_name,

                    'total_qty' => $totalQty,

                    'purchase_price' => round(
                        $items->avg('actual_cost_price'),
                        2
                    ),

                    'sale_price' => round(
                        $items->avg('sale_price'),
                        2
                    ),

                    'total_cost' => $totalCost,

                    'total_sale' => $totalSale,

                    'profit_loss' => $totalSale - $totalCost,

                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | OLD PRODUCT
        |--------------------------------------------------------------------------
        */
        $oldProducts = DB::table('old_product_sale_details as osd')
            ->join('old_product_sales as os', 'os.id', '=', 'osd.sale_id')
            ->join('old_products as op', 'op.id', '=', 'osd.old_product_id')
            ->whereBetween('os.sale_date', [$startDate, $endDate])
            ->select(
                'op.product_name',

                DB::raw('SUM(osd.qty) as total_qty'),

                DB::raw('SUM(osd.qty * osd.buy_price) as total_cost'),

                DB::raw('SUM(osd.subtotal) as total_sale'),

                DB::raw('AVG(osd.buy_price) as purchase_price'),

                DB::raw('AVG(osd.price) as sale_price'),

                DB::raw('SUM(osd.subtotal) - SUM(osd.qty * osd.buy_price) as profit_loss')
            )
            ->groupBy('op.product_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EXPENSES
        |--------------------------------------------------------------------------
        */
        $expenses = DB::table('expenses')
        ->whereBetween('expense_date', [$startDate, $endDate])
        ->sum('total');

    $transport = DB::table('purchases')
        ->whereBetween('date', [$startDate, $endDate])
        ->sum('shipping_amount');

    $totalSale = $newProducts->sum('total_sale')
        + $oldProducts->sum('total_sale');

    $totalCost = $newProducts->sum('total_cost')
        + $oldProducts->sum('total_cost');

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    | Shipping already included in product cost
    | therefore expense table contains ONLY daily expense
    |--------------------------------------------------------------------------
    */

    $grossProfit = $totalSale - $totalCost;

    $totalExpense = $expenses;

    $netProfit = $grossProfit - $expenses;

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */
        $totalSale =
            $newProducts->sum('total_sale')
            +
            $oldProducts->sum('total_sale');

        $totalCost =
            $newProducts->sum('total_cost')
            +
            $oldProducts->sum('total_cost');

        $grossProfit = $totalSale - $totalCost;

        $netProfit =
            $grossProfit
            -
            $expenses;

        return view(
            'reports.profit_loss',
            compact(
                'startDate',
                'endDate',
                'newProducts',
                'oldProducts',
                'expenses',
                'transport',
                'totalSale',
                'totalCost',
                'grossProfit',
                'netProfit'
            )
        );
    }


    public function profitLossPdf(Request $request)
    {
        $startDate = Carbon::parse($request->start_date ?? now()->startOfMonth()->toDateString())
            ->startOfDay();

        $endDate = Carbon::parse($request->end_date ?? now()->endOfMonth()->toDateString())
            ->endOfDay();
        $company = DB::table('company_info')->first();
        /*
        |--------------------------------------------------------------------------
        | NEW PRODUCTS (FIXED LOGIC SAME AS WEB)
        |--------------------------------------------------------------------------
        */
        $newProductsRaw = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->join('products as p', 'p.id', '=', 'sd.product_id')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->select(
                'sd.product_id',
                'p.product_name',
                'sd.qty',
                'sd.price as sale_price',
                'sd.total as sale_total',
                's.sale_date',

                DB::raw("
                    (
                        SELECT COALESCE(AVG(pd.price), p.purchase_price)
                        FROM purchase_details pd
                        JOIN purchases pu ON pu.id = pd.purchase_id
                        WHERE pd.product_id = sd.product_id
                        AND pu.date <= s.sale_date
                    ) as purchase_price
                ")
            )
            ->get();

        $newProducts = $newProductsRaw
            ->groupBy('product_id')
            ->map(function ($items) {

                $first = $items->first();

                $totalQty = $items->sum('qty');
                $totalSale = $items->sum('sale_total');

                $totalCost = $items->sum(function ($row) {
                    return $row->qty * ($row->purchase_price ?? 0);
                });

                $profit = $totalSale - $totalCost;

                return (object) [
                    'product_name'   => $first->product_name,
                    'total_qty'      => $totalQty,
                    'total_sale'     => $totalSale,
                    'total_cost'     => $totalCost,
                    'purchase_price' => $items->avg('purchase_price'),
                    'sale_price'     => $items->avg('sale_price'),
                    'profit_loss'    => $profit,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | OLD PRODUCTS
        |--------------------------------------------------------------------------
        */
        $oldProducts = DB::table('old_product_sale_details as osd')
            ->join('old_product_sales as os', 'os.id', '=', 'osd.sale_id')
            ->join('old_products as op', 'op.id', '=', 'osd.old_product_id')
            ->whereBetween('os.sale_date', [$startDate, $endDate])
            ->select(
                'op.product_name',
                DB::raw('SUM(osd.qty) as total_qty'),
                DB::raw('SUM(osd.qty * osd.buy_price) as total_cost'),
                DB::raw('SUM(osd.subtotal) as total_sale'),
                DB::raw('SUM(osd.subtotal) - SUM(osd.qty * osd.buy_price) as profit_loss')
            )
            ->groupBy('op.product_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | EXPENSES
        |--------------------------------------------------------------------------
        */
        $expenses = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('total');

        $transport = DB::table('purchases')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('shipping_amount');

        /*
        |--------------------------------------------------------------------------
        | TOTALS
        |--------------------------------------------------------------------------
        */
        $totalSale = $newProducts->sum('total_sale') + $oldProducts->sum('total_sale');
        $totalCost = $newProducts->sum('total_cost') + $oldProducts->sum('total_cost');

        $grossProfit = $totalSale - $totalCost;
        $netProfitFinal = $grossProfit - ($expenses + $transport);

        /*
        |--------------------------------------------------------------------------
        | mPDF SETUP
        |--------------------------------------------------------------------------
        */
        $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',

        'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
            public_path('fonts')
        ]),

        'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
            'solaimanlipi' => [
                'R' => 'SolaimanLipi.ttf',
                'B' => 'SolaimanLipi.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ]
        ],

        'default_font' => 'solaimanlipi',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'tempDir' => storage_path('temp'),
    ]);

        $html = view('reports.profit_loss_pdf', compact(
            'startDate',
            'endDate',
            'newProducts',
            'oldProducts',
            'expenses',
            'transport',
            'totalSale',
            'totalCost',
            'grossProfit',
            'netProfitFinal',
            'company'
        ))->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('profit-loss-report.pdf', 'I');
    }
}