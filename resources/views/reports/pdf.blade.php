<h2>Product-wise Profit & Loss Report</h2>
<p>From: {{ $start }} To: {{ $end }}</p>
<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Sold Qty</th>
            <th>Sold Amount</th>
            <th>Purchase Qty</th>
            <th>Purchase Amount</th>
            <th>Profit / Loss</th>
        </tr>
    </thead>
    <tbody>
        @foreach($productReports as $key=>$report)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ $report['product_name'] }}</td>
            <td>{{ $report['sold_qty'] }}</td>
            <td>{{ number_format($report['sold_amount'],2) }}</td>
            <td>{{ $report['purchase_qty'] }}</td>
            <td>{{ number_format($report['purchase_amount'],2) }}</td>
            <td>{{ number_format($report['profit_loss'],2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>