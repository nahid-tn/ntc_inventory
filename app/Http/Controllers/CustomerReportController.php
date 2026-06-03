<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerSalesExport;

class CustomerReportController extends Controller
{
    /**
     * mPDF ইনস্ট্যান্স তৈরি করার হেল্পার
     */
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

    // ==============================
    // All Customers Report
    // ==============================
    public function allCustomersReport()
    {
        $customers = Customer::with(['sales', 'oldProductSales'])->get();

        $report = $customers->map(function($customer) {
            $newSalesTotal = $customer->sales->sum('total_amount');
            $newSalesPaid  = $customer->sales->sum('paid_amount');
            $newSalesDue   = $customer->sales->sum('due_amount');

            $oldSalesTotal = $customer->oldProductSales->sum('total_amount');
            $oldSalesPaid  = $customer->oldProductSales->sum('paid_amount');
            $oldSalesDue   = $customer->oldProductSales->sum('due_amount');

            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'mobile' => $customer->mobile,
                'total_new_sales' => $newSalesTotal,
                'total_new_paid'  => $newSalesPaid,
                'total_new_due'   => $newSalesDue,
                'total_old_sales' => $oldSalesTotal,
                'total_old_paid'  => $oldSalesPaid,
                'total_old_due'   => $oldSalesDue,
                'total_sales'     => $newSalesTotal + $oldSalesTotal,
                'total_paid'      => $newSalesPaid + $oldSalesPaid,
                'total_due'       => $newSalesDue + $oldSalesDue,
            ];
        });

        return view('customerReport.customer_all', compact('report'));
    }

    // ==============================
    // Single Customer Report
    // ==============================
    public function singleCustomerReport($customerId)
    {
        $customer = Customer::with(['sales', 'oldProductSales'])->findOrFail($customerId);
        return view('customerReport.customer_single', compact('customer'));
    }

    public function salesCustomerReport($customerId)
    {
        $customer = Customer::with(['sales', 'oldProductSales'])->findOrFail($customerId);
        return view('customerReport.customer_single', compact('customer'));
    }

    // ==============================
    // PDF Export - mPDF দিয়ে
    // ==============================
    public function pdf($customerId)
    {
        $customer = Customer::with(['sales', 'oldProductSales'])->findOrFail($customerId);

        $html = view('customerReport.customer_sales_pdf', compact('customer'))->render();
        
        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html);
        
        $currentDate = Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = $customer->id . '_' . $customer->customer_name . '_' . $currentDate . '_customer_ledger_SalesReport.pdf';

        return $mpdf->Output($fileName, 'I'); // I = Browser এ দেখাবে, D = Download
    }

    // ==============================
    // Excel Export
    // ==============================
    public function excel($customerId)
    {
        return Excel::download(new CustomerSalesExport($customerId), 'customer_sales_'.$customerId.'.xlsx');
    }

    public function dueReports(Request $request)
    {
        $reportType     = $request->report_type;
        $customerFilter = $request->customer;
        $invoiceFilter  = $request->invoice;
        $fromDate       = $request->from_date;
        $toDate         = $request->to_date;
        $dueMin         = $request->due_min;
        $dueMax         = $request->due_max;

        $customers = collect();
        $invoices  = collect();
        $salesPayments = collect();

        /* =========================
        Customer Wise Due
        ========================= */
        if($reportType == "customer" || $reportType == null){

            $query = DB::table('customers as c')
            ->leftJoin('sales as s','c.id','=','s.customer_id')
            ->leftJoin(
                DB::raw('(SELECT sale_id, SUM(amount) as total_received
                        FROM sales_received
                        GROUP BY sale_id) as sr'),
                'sr.sale_id','=','s.id'
            )
            ->select(
                'c.id',
                'c.customer_name',
                'c.mobile',
                DB::raw('SUM(IFNULL(s.total_amount,0)) as total_sales'),
                DB::raw('SUM(IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0)) as total_paid'),
                DB::raw('SUM(IFNULL(s.total_amount,0) - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) as total_due')
            )
            ->groupBy('c.id','c.customer_name','c.mobile')
            ->havingRaw('SUM(IFNULL(s.total_amount,0) - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) > 0');

            if($customerFilter){
                $query->where(function($q) use ($customerFilter){
                    $q->where('c.mobile','like',"%{$customerFilter}%")
                    ->orWhere('c.customer_name','like',"%{$customerFilter}%");
                });
            }

            if($fromDate){
                $query->whereDate('s.sale_date','>=',$fromDate);
            }

            if($toDate){
                $query->whereDate('s.sale_date','<=',$toDate);
            }

            if($dueMin){
                $query->havingRaw('SUM(IFNULL(s.total_amount,0) - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) >= ?',[$dueMin]);
            }

            if($dueMax){
                $query->havingRaw('SUM(IFNULL(s.total_amount,0) - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) <= ?',[$dueMax]);
            }

            $customers = $query->get();
        }

        /* =========================
        Invoice Wise Due
        ========================= */
        if($reportType == "invoice" || $reportType == null){

            $query = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->leftJoin(
                DB::raw('(SELECT sale_id, SUM(amount) as total_received
                        FROM sales_received
                        GROUP BY sale_id) as sr'),
                'sr.sale_id','=','s.id'
            )
            ->select(
                's.id',
                's.invoice_no',
                'c.customer_name',
                's.sale_date',
                's.total_amount',
                's.paid_amount',
                DB::raw('IFNULL(sr.total_received,0) as total_received'),
                DB::raw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) as due')
            )
            ->whereRaw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) > 0')
            ->orderBy('s.sale_date','asc');

            if($customerFilter){
                $query->where(function($q) use ($customerFilter){
                    $q->where('c.mobile','like',"%{$customerFilter}%")
                    ->orWhere('c.customer_name','like',"%{$customerFilter}%");
                });
            }

            if($invoiceFilter){
                $query->where('s.invoice_no','like',"%{$invoiceFilter}%");
            }

            if($fromDate){
                $query->whereDate('s.sale_date','>=',$fromDate);
            }

            if($toDate){
                $query->whereDate('s.sale_date','<=',$toDate);
            }

            if($dueMin){
                $query->whereRaw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) >= ?',[$dueMin]);
            }

            if($dueMax){
                $query->whereRaw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) <= ?',[$dueMax]);
            }

            $invoices = $query->get();
        }

        /* =========================
        Invoice + Payment History
        ========================= */
        if($reportType == "payment" || $reportType == null){

            $query = DB::table('sales as s')
            ->join('customers as c','c.id','=','s.customer_id')
            ->leftJoin(
                DB::raw('(SELECT sale_id, SUM(amount) as total_received
                        FROM sales_received
                        GROUP BY sale_id) as sr'),
                'sr.sale_id','=','s.id'
            )
            ->select(
                's.id',
                's.invoice_no',
                'c.customer_name',
                's.sale_date',
                's.total_amount',
                's.paid_amount',
                DB::raw('IFNULL(sr.total_received,0) as total_received'),
                DB::raw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) as due')
            )
            ->orderBy('s.sale_date','asc');

            if($customerFilter){
                $query->where(function($q) use ($customerFilter){
                    $q->where('c.mobile','like',"%{$customerFilter}%")
                    ->orWhere('c.customer_name','like',"%{$customerFilter}%");
                });
            }

            if($invoiceFilter){
                $query->where('s.invoice_no','like',"%{$invoiceFilter}%");
            }

            if($fromDate){
                $query->whereDate('s.sale_date','>=',$fromDate);
            }

            if($toDate){
                $query->whereDate('s.sale_date','<=',$toDate);
            }

            if($dueMin){
                $query->whereRaw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) >= ?',[$dueMin]);
            }

            if($dueMax){
                $query->whereRaw('(s.total_amount - (s.paid_amount + IFNULL(sr.total_received,0))) <= ?',[$dueMax]);
            }

            $salesPayments = $query->get();
        }

        return view('reports.due_reports', compact(
            'customers',
            'invoices',
            'salesPayments',
            'reportType',
            'customerFilter',
            'invoiceFilter',
            'fromDate',
            'toDate',
            'dueMin',
            'dueMax'
        ));
    }

    public function dueReportsPdf(Request $request)
    {
        $customerFilter = $request->customer;
        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        /* =========================
           INVOICE WISE DATA
        ========================= */
        $sales = DB::table('sales as s')
            ->join('customers as c', 'c.id', '=', 's.customer_id')
            ->leftJoin(DB::raw('(
                SELECT sale_id, SUM(amount) as total_received
                FROM sales_received
                GROUP BY sale_id
            ) as sr'), 'sr.sale_id', '=', 's.id')
            ->select(
                's.id',
                's.invoice_no',
                's.sale_date',
                'c.id as customer_id',
                'c.customer_name',
                'c.mobile',
                's.total_amount',
                's.paid_amount',
                DB::raw('IFNULL(sr.total_received,0) as received'),
                DB::raw('(s.total_amount - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) as due')
            )
            ->when($customerFilter, function ($q) use ($customerFilter) {
                $q->where(function ($r) use ($customerFilter) {
                    $r->where('c.customer_name', 'like', "%$customerFilter%")
                      ->orWhere('c.mobile', 'like', "%$customerFilter%");
                });
            })
            ->when($fromDate, fn($q) => $q->whereDate('s.sale_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('s.sale_date', '<=', $toDate))
            ->orderBy('s.sale_date', 'asc')
            ->get();

        /* =========================
           CUSTOMER SUMMARY
        ========================= */
        $customers = DB::table('customers as c')
            ->leftJoin('sales as s', 'c.id', '=', 's.customer_id')
            ->leftJoin(DB::raw('(
                SELECT sale_id, SUM(amount) as total_received
                FROM sales_received
                GROUP BY sale_id
            ) as sr'), 'sr.sale_id', '=', 's.id')
            ->select(
                'c.id',
                'c.customer_name',
                'c.mobile',
                'c.address',
                DB::raw('SUM(IFNULL(s.total_amount,0)) as total_sales'),
                DB::raw('SUM(IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0)) as total_paid'),
                DB::raw('SUM(IFNULL(s.total_amount,0) - (IFNULL(s.paid_amount,0) + IFNULL(sr.total_received,0))) as total_due')
            )
            ->when($customerFilter, function ($q) use ($customerFilter) {
                $q->where(function ($r) use ($customerFilter) {
                    $r->where('c.customer_name', 'like', "%$customerFilter%")
                      ->orWhere('c.mobile', 'like', "%$customerFilter%");
                });
            })
            ->groupBy('c.id', 'c.customer_name', 'c.mobile', 'c.address')
            ->get();

        /* =========================
           TOTAL SUMMARY (GLOBAL)
        ========================= */
        $totalSales = $sales->sum('total_amount');
        $totalPaid  = $sales->sum(function($row){
            return ($row->paid_amount ?? 0) + ($row->received ?? 0);
        });
        $totalDue   = $sales->sum('due');

        /* =========================
           mPDF
        ========================= */
        $currentDate = Carbon::now()->format('Ymd');
        $fileName = 'Supplier_Of_' . ($sales->first()->customer_id ?? 'All') . '_' . ($sales->first()->customer_name ?? 'All') . '_' . $currentDate . '_ledger.pdf';
        
        $logo = '';
        if (file_exists(public_path('images/logo.png'))) {
            $logo = base64_encode(file_get_contents(public_path('images/logo.png')));
        }

        $html = view('reports.due_reports_pdf', compact(
            'sales',
            'customers',
            'totalSales',
            'totalPaid',
            'totalDue',
            'customerFilter',
            'fromDate',
            'toDate',
            'logo'
        ))->render();

        $mpdf = $this->createMpdf();
        $mpdf->WriteHTML($html);

        return $mpdf->Output($fileName, 'I');
    }
}