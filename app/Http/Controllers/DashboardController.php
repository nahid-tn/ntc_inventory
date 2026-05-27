<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // -------------------------
        // Total Sales
        // -------------------------
        $totalSaleNew = DB::table('sales')->sum('total_amount');
        $totalSaleOld = DB::table('old_product_sales')->sum('total_amount');
        $totalSale = $totalSaleNew + $totalSaleOld;

        // -------------------------
        // Total Purchases
        // -------------------------
        $totalNewPurchase = DB::table('purchases')->sum('subtotal');
        $totalOldPurchase = DB::table('old_product_purchases')->sum('total');
        $totalPurchase = $totalNewPurchase + $totalOldPurchase;

        // -------------------------
        // Total Profit (Sale - Purchase)
        // -------------------------
        $totalProfit = $totalSale - $totalPurchase;

        // -------------------------
        // Products Info
        // -------------------------
        $totalProduct = DB::table('products')->sum('stock'); 
        $lowStock = DB::table('products')->where('stock', '<', 2)->count();

        // -------------------------
        // Expense Summary
        // -------------------------
        $expenseToday = Expense::whereDate('expense_date', Carbon::today())->sum('total');
        $expenseMonth = Expense::whereYear('expense_date', Carbon::now()->year)
                                ->whereMonth('expense_date', Carbon::now()->month)
                                ->sum('total');
        $expenseTotal = Expense::sum('total');

        // -------------------------
        // Monthly Sale Chart (Jan-Dec)
        // -------------------------
        $saleLabels = [];
        $saleData = [];
        for ($m = 1; $m <= 12; $m++) {
            $saleLabels[] = Carbon::create()->month($m)->format('M');

            $monthSaleNew = DB::table('sales')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount');

            $monthSaleOld = DB::table('old_product_sales')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total_amount');

            $saleData[] = $monthSaleNew + $monthSaleOld;
        }

        // -------------------------
        // Monthly Expense Chart (Jan-Dec)
        // -------------------------
        $expenseLabels = [];
        $expenseData = [];
        for ($m = 1; $m <= 12; $m++) {
            $expenseLabels[] = Carbon::create()->month($m)->format('M');
            $expenseMonthSum = Expense::whereMonth('expense_date', $m)
                                      ->whereYear('expense_date', Carbon::now()->year)
                                      ->sum('total');
            $expenseData[] = $expenseMonthSum;
        }

        // -------------------------
        // Monthly Purchase Chart (Jan-Dec)
        // -------------------------
        $purchaseLabels = [];
        $purchaseData = [];
        for ($m = 1; $m <= 12; $m++) {
            $purchaseLabels[] = Carbon::create()->month($m)->format('M');

            $monthPurchaseNew = DB::table('purchases')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('subtotal');

            $monthPurchaseOld = DB::table('old_product_purchases')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('total');

            $purchaseData[] = $monthPurchaseNew + $monthPurchaseOld;
        }

        // -------------------------
        // Return view
        // -------------------------
        return view('Admin.dashboard', compact(
            'totalSale',
            'totalProfit',
            'totalProduct',
            'lowStock',
            'expenseToday',
            'expenseMonth',
            'expenseTotal',
            'totalPurchase',
            'saleLabels',
            'saleData',
            'expenseLabels',
            'expenseData',
            'purchaseLabels',
            'purchaseData'
        ));
    }
}