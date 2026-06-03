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
        /*

        Date Range

        */
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        /*

        NEW PRODUCTS

        */
        $newProductsRaw = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->join('products as p', 'p.id', '=', 'sd.product_id')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->select(
                'sd.product_id',
                'p.product_name',
                'sd.qty',
                'sd.price',
                'sd.total',
                's.sale_date',
                DB::raw("(
                    SELECT pd.price
                    FROM purchase_details pd
                    INNER JOIN purchases pu ON pu.id = pd.purchase_id
                    WHERE pd.product_id = sd.product_id
                    AND pu.date <= s.sale_date
                    ORDER BY pu.date DESC, pd.id DESC
                    LIMIT 1
                ) as purchase_price")
            )
            ->get();

        $newProducts = $newProductsRaw
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                $totalQty = $items->sum('qty');
                $totalSale = $items->sum('total');
                $salePrice = $items->avg('price');

                $totalCost = $items->sum(function ($row) {
                    $purchasePrice = $row->purchase_price ?? 0;
                    return $row->qty * $purchasePrice;
                });

                $avgPurchasePrice = $items->avg(function ($row) {
                    return $row->purchase_price ?? 0;
                });

                $profitLoss = $totalSale - $totalCost;

                return (object) [
                    'product_name'   => $first->product_name,
                    'purchase_price' => $avgPurchasePrice,
                    'sale_price'     => $salePrice,
                    'total_qty'      => $totalQty,
                    'total_cost'     => $totalCost,
                    'total_sale'     => $totalSale,
                    'profit_loss'    => $profitLoss,
                ];
            })
            ->values();

        /*

        NEW PRODUCT TOTALS

        */
        $newProductTotalQty = $newProducts->sum('total_qty');
        $newProductTotalPurchase = $newProducts->sum('purchase_price');
        $newProductTotalCost = $newProducts->sum('total_cost');
        $newProductTotalSalePrice = $newProducts->sum('sale_price');
        $newProductTotalSale = $newProducts->sum('total_sale');
        $newProductTotalProfit = $newProducts->sum('profit_loss');

        /*

        OLD PRODUCTS

        */
        $oldProducts = DB::table('old_product_sale_details as osd')
            ->join('old_product_sales as os', 'os.id', '=', 'osd.sale_id')
            ->join('old_products as op', 'op.id', '=', 'osd.old_product_id')
            ->whereBetween('os.sale_date', [$startDate, $endDate])
            ->select(
                'op.product_name',
                DB::raw('AVG(osd.buy_price) as purchase_price'),
                DB::raw('AVG(osd.price) as sale_price'),
                DB::raw('SUM(osd.qty) as total_qty'),
                DB::raw('SUM(osd.qty * osd.buy_price) as total_cost'),
                DB::raw('SUM(osd.subtotal) as total_sale'),
                DB::raw('SUM(osd.subtotal) - SUM(osd.qty * osd.buy_price) as profit_loss')
            )
            ->groupBy('op.product_name')
            ->get();

        /*

        EXPENSE

        */
        $expenses = DB::table('expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('total');

        /*

        TRANSPORT

        */
        $transport = DB::table('purchases')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('shipping_amount');

        /*

        FINAL TOTAL

        */
        $totalSale = $newProducts->sum('total_sale') + $oldProducts->sum('total_sale');
        $totalCost = $newProducts->sum('total_cost') + $oldProducts->sum('total_cost');
        $grossProfit = $totalSale - $totalCost;
        $netProfit = $grossProfit - $expenses - $transport;

        return view('reports.profit_loss', compact(
            'startDate',
            'endDate',
            'newProducts',
            'oldProducts',
            'expenses',
            'transport',
            'totalSale',
            'totalCost',
            'grossProfit',
            'netProfit',
            'newProductTotalQty',
            'newProductTotalPurchase',
            'newProductTotalCost',
            'newProductTotalSalePrice',
            'newProductTotalSale',
            'newProductTotalProfit'
        ));
    }

    public function profitLossPdf(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $newProductsRaw = DB::table('sale_details as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->join('products as p', 'p.id', '=', 'sd.product_id')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->select(
                'sd.product_id',
                'p.product_name',
                'sd.qty',
                'sd.total',
                's.sale_date',
                DB::raw("(
                    SELECT pd.price
                    FROM purchase_details pd
                    INNER JOIN purchases pu ON pu.id = pd.purchase_id
                    WHERE pd.product_id = sd.product_id
                    AND pu.date <= s.sale_date
                    ORDER BY pu.date DESC, pd.id DESC
                    LIMIT 1
                ) as purchase_price")
            )
            ->get();

        $newProducts = $newProductsRaw
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();
                $totalQty = $items->sum('qty');
                $totalSale = $items->sum('total');
                $totalCost = $items->sum(function ($row) {
                    $purchasePrice = $row->purchase_price ?? 0;
                    return $row->qty * $purchasePrice;
                });

                return (object) [
                    'product_name'   => $first->product_name,
                    'total_qty'      => $totalQty,
                    'total_sale'     => $totalSale,
                    'total_cost'     => $totalCost,
                    'purchase_price' => $first->purchase_price ?? 0,
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
                DB::raw('SUM(osd.subtotal) as total_sale')
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
        | FINAL CALCULATION
        |--------------------------------------------------------------------------
        */
        $totalSale = $newProducts->sum('total_sale') + $oldProducts->sum('total_sale');
        $totalCost = $newProducts->sum('total_cost') + $oldProducts->sum('total_cost');
        $totalExpense = $expenses + $transport;
        $grossProfit = $totalSale - $totalCost;
        $netProfitFinal = $grossProfit - $totalExpense;

        /*
        |--------------------------------------------------------------------------
        | mPDF - বাংলা সাপোর্ট
        |--------------------------------------------------------------------------
        */
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
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
            'totalExpense',
            'grossProfit',
            'netProfitFinal'
        ))->render();

        $mpdf->WriteHTML($html);
        return $mpdf->Output('profit-loss-report.pdf', 'I');
    }
}