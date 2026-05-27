<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\OldProductSale;
use App\Models\SalesReceived;
use Carbon\Carbon;
use DB;
use App\Models\OldProductSaleDetail;

class SalesReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();
        $newSales = Sales::whereDate('sale_date',$today)->get();
        $oldSales = OldProductSale::whereDate('sale_date',$today)->get();
        return view('report.sales_report',[
            'newSales'=>$newSales,
            'oldSales'=>$oldSales,
            'type'=>'daily',
            'date'=>$today
        ]);
    }

    public function filter(Request $request)
    {

        $type = $request->type;

        if($type=='daily'){

            $date = $request->date ?? Carbon::today();

            $newSales = Sales::whereDate('sale_date',$date)->get();

            $oldSales = OldProductSale::whereDate('sale_date',$date)->get();

        }

        elseif($type=='monthly'){

            $month = $request->month;

            $newSales = Sales::whereMonth('sale_date',Carbon::parse($month)->month)
                        ->whereYear('sale_date',Carbon::parse($month)->year)
                        ->get();

            $oldSales = OldProductSale::whereMonth('sale_date',Carbon::parse($month)->month)
                        ->whereYear('sale_date',Carbon::parse($month)->year)
                        ->get();

        }

        elseif($type=='yearly'){

            $year = $request->year;

            $newSales = Sales::whereYear('sale_date',$year)->get();

            $oldSales = OldProductSale::whereYear('sale_date',$year)->get();
        }

        return view('report.sales_report',[
            'newSales'=>$newSales,
            'oldSales'=>$oldSales,
            'type'=>$type
        ]);

    }

    public function dashboard()
    {
        
        // ======================
        // CURRENT DATE & MONTH
        // ======================
        $today = Carbon::today();
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        // ======================
        // DAILY REPORT
        // ======================
        $dailySales = Sales::whereDate('sale_date', $today)->sum('total_amount')
                    + OldProductSale::whereDate('sale_date', $today)->sum('total_amount');

        $dailyDue = Sales::whereDate('sale_date', $today)->sum('due_amount')
                + OldProductSale::whereDate('sale_date', $today)->sum('due_amount');

        $dailyReceived = SalesReceived::whereDate('received_date', $today)->sum('amount');

        $monthlySalesTotal = Sales::whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->sum('total_amount')
                            + OldProductSale::whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->sum('total_amount');

        $monthlyDue = Sales::whereMonth('sale_date', $month)
                        ->whereYear('sale_date', $year)
                        ->sum('due_amount')
                        + OldProductSale::whereMonth('sale_date', $month)
                        ->whereYear('sale_date', $year)
                        ->sum('due_amount');

        $monthlyReceived = SalesReceived::whereMonth('received_date', $month)
                            ->whereYear('received_date', $year)
                            ->sum('amount');

        $monthlySalesChart = Sales::selectRaw('MONTH(sale_date) as month, SUM(total_amount) as total')
                            ->groupBy('month')
                            ->pluck('total','month');

        return view('report.dashboard', compact(
            'dailySales',
            'dailyDue',
            'dailyReceived',
            'monthlySalesTotal',
            'monthlyDue',
            'monthlyReceived',
            'monthlySalesChart'
        ));
    }

    public function productSales()
    {
        $products = DB::table('sale_details')
        ->join('products','sale_details.product_id','=','products.id')
        ->select(
        'products.product_name',
        DB::raw('SUM(sale_details.qty) as total_qty'),
        DB::raw('SUM(sale_details.total) as total_sales')
        )
        ->groupBy('products.product_name')
        ->get();

        return view('report.product_sales',compact('products'));

    }


    /* =========================
    Profit Report
    ========================= */

    public function profitReport()
    {

    $totalOldProfit = OldProductSaleDetail::sum('profit');

    $totalSales = Sales::sum('total_amount');

    return view('report.profit',compact(
    'totalOldProfit',
    'totalSales'
    ));

    }

    public function exportPdf()
    {
        $sales = Sales::with('customer')->get();
        $pdf = PDF::loadView('report.sales_pdf',compact('sales'));
        return $pdf->download('sales_report.pdf');

    }
    
}
