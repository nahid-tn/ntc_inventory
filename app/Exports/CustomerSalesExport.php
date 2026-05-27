<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerSalesExport implements FromArray, WithHeadings
{
    protected $customerId;

    public function __construct($customerId)
    {
        $this->customerId = $customerId;
    }

    public function array(): array
    {
        $customer = Customer::with(['sales', 'oldProductSales'])->findOrFail($this->customerId);
        $data = [];

        // New Products Sales
        foreach ($customer->sales as $sale) {
            $data[] = [
                'Invoice No' => $sale->invoice_no,
                'Sale Date'  => $sale->sale_date->format('d-m-Y'),
                'Subtotal'   => $sale->subtotal,
                'Tax'        => $sale->tax_amount,
                'Discount'   => $sale->discount_amount,
                'Total'      => $sale->total_amount,
                'Paid'       => $sale->paid_amount,
                'Due'        => $sale->due_amount,
                'Type'       => 'New',
            ];
        }

        // Old Products Sales
        foreach ($customer->oldProductSales as $sale) {
            $data[] = [
                'Invoice No' => $sale->invoice_no,
                'Sale Date'  => $sale->sale_date->format('d-m-Y'),
                'Subtotal'   => '',
                'Tax'        => '',
                'Discount'   => '',
                'Total'      => $sale->total_amount,
                'Paid'       => $sale->paid_amount,
                'Due'        => $sale->due_amount,
                'Type'       => 'Old',
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Invoice No', 'Sale Date', 'Subtotal', 'Tax', 'Discount', 'Total', 'Paid', 'Due', 'Type'];
    }
}