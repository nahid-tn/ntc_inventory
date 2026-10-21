<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SaleDetail;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\SupplierPayment;
use App\Models\OldProduct;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end   = $request->end_date ?? now()->endOfMonth()->toDateString();
        $products = \App\Models\Product::all();
        $report = [];
        foreach ($products as $product) {
            $purchaseQuery = \App\Models\PurchaseDetail::where('product_id', $product->id)
                ->whereHas('purchase', function ($q) use ($start, $end) {
                    $q->whereBetween('date', [$start, $end]);
                });
            $purchaseQty = $purchaseQuery->sum('qty');
            $purchaseAmount = $purchaseQuery->sum('total');
            $saleQuery = \App\Models\SaleDetail::where('product_id', $product->id)
                ->whereHas('sale', function ($q) use ($start, $end) {
                    $q->whereBetween('sale_date', [$start, $end]);
                });
            $saleQty = $saleQuery->sum('qty');
            $saleAmount = $saleQuery->sum('total');
            $grossProfit = $saleAmount - $purchaseAmount;
            if ($purchaseQty > 0 || $saleQty > 0) {
                $report[] = [
                    'product_name' => $product->product_name,
                    'purchase_qty' => $purchaseQty,
                    'purchase_amount' => $purchaseAmount,
                    'sale_qty' => $saleQty,
                    'sale_amount' => $saleAmount,
                    'gross_profit' => $grossProfit,
                ];
            }
        }

        $totalExpense = \App\Models\Expense::whereBetween('expense_date', [$start, $end])
                            ->sum('total');
        $totalPurchase = collect($report)->sum('purchase_amount');
        $totalSale = collect($report)->sum('sale_amount');
        $totalGrossProfit = $totalSale - $totalPurchase;
        $netProfit = $totalGrossProfit - $totalExpense;
        return view('reports.index', compact(
            'report',
            'start',
            'end',
            'totalExpense',
            'totalPurchase',
            'totalSale',
            'totalGrossProfit',
            'netProfit'
        ));
    }


    public function dashboard()
    {
        $totalPurchase = Purchase::sum('total_amount');
        $totalPaid = SupplierPayment::sum('amount');
        $totalDue = $totalPurchase - $totalPaid;
        $todayPurchase = Purchase::whereDate('date', today())->sum('total_amount');
        $todayPaid = SupplierPayment::whereDate('created_at', today())->sum('amount');
        $todayDue = $todayPurchase - $todayPaid;
        $monthlyPurchase = Purchase::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthlyPaid = SupplierPayment::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $monthlyDue = $monthlyPurchase - $monthlyPaid;
        $supplierDue = Purchase::select('supplier_id', DB::raw('SUM(total_amount) as total_purchase'),)->groupBy('supplier_id')->with('supplier')->get();
        
        return view('purchase.dashboard', compact(
            'totalPurchase',
            'totalDue',
            'todayPurchase',
            'todayDue',
            'monthlyPurchase',
            'monthlyDue',
            'supplierDue'
        ));
    }

    public function stockReport()
    {
        $stocks = DB::table('products')
    ->leftJoin('purchase_details', 'purchase_details.product_id', '=', 'products.id')
    ->select(
        'products.id',
        'products.product_name',
        'products.purchase_price',
        'products.selling_price',
        DB::raw('COALESCE(SUM(purchase_details.qty),0) as total_qty'),
        DB::raw('CAST(products.stock AS UNSIGNED) as stock')
    )
    ->groupBy(
        'products.id',
        'products.product_name',
        'products.purchase_price',
        'products.selling_price',
        'products.stock'
    )
    ->paginate(15);

        return view('purchase.stock_report', compact('stocks'));
    }

    
    public function supplierPayments(Request $request)
    {
        $query = Supplier::query();

        if ($request->supplier_id) {
            $query->where('id', $request->supplier_id);
        }

        $suppliers = $query->with([
            'payments' => function ($q) use ($request) {
                if ($request->from_date && $request->to_date) {
                    $q->whereBetween('payment_date', [
                        $request->from_date,
                        $request->to_date
                    ]);
                }
            },
            'purchases' => function ($q) use ($request) {
                if ($request->from_date && $request->to_date) {
                    $q->whereBetween('date', [
                        $request->from_date,
                        $request->to_date
                    ]);
                }
            },
            'returns' => function ($q) use ($request) {
                if ($request->from_date && $request->to_date) {
                    $q->whereBetween('return_date', [
                        $request->from_date,
                        $request->to_date
                    ]);
                }
            },
        ])->get();

        $reports = $suppliers->map(function ($s) {

            $totalPurchase = $s->purchases->sum('total_amount');
            $purchasePayment = $s->purchases->sum('paid_amount');
            $supplierPayment = $s->payments->sum('amount');
            $totalReturn = $s->returns->sum('total_amount');

            $totalPurchasesPay = $supplierPayment + $purchasePayment;
            $totalPayment = $supplierPayment + $totalReturn;
            $due = $totalPurchase - $totalPayment;

            return [
                'supplier'           => $s,
                'payments'           => $s->payments,
                'purchases'          => $s->purchases,
                'returns'            => $s->returns,
                'supplierPayment'    => $supplierPayment,
                'purchasePayment'    => $purchasePayment,
                'totalPurchasesPay'  => $totalPurchasesPay,
                'totalPayment'       => $totalPayment,
                'totalPurchase'      => $totalPurchase,
                'totalReturn'        => $totalReturn,
                'due'                => $due,
            ];
        });

        // PDF Export Using mPDF
        if ($request->has('pdf')) {

            $html = view(
                'reports.supplier_payments_pdf',
                compact('reports')
            )->render();

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
            ]);

            $mpdf->WriteHTML($html);

            return response(
                $mpdf->Output(
                    'supplier-payments.pdf',
                    \Mpdf\Output\Destination::STRING_RETURN
                ),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="supplier-payments.pdf"',
                ]
            );
        }

        return view('reports.supplier_payments', compact('reports'));
    }
}