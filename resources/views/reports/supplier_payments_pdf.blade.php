<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Supplier Payment Report</title>

<style>

body{
    font-family: DejaVu Sans;
    font-size:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #000;
    padding:6px;
    text-align:right;
}

th{
    background:#eee;
}

td:first-child,
th:first-child{
    text-align:left;
}

h3{
    text-align:center;
}

</style>

</head>

<body>


<h3>Supplier Payment Report</h3>


<table>

<thead>

<tr>
    <th>Supplier</th>
    <th>Purchase Payment</th>
    <th>Later Payment</th>
    <th>Total Payment</th>
    <th>Total Purchase</th>
    <th>Total Return</th>
</tr>

</thead>

<tbody>

@foreach($reports as $row)

<tr>

    <td>{{ $row['supplier']->supplier_name }}</td>

    <td>{{ number_format($row['purchasePayment'],2) }}</td>

    <td>{{ number_format($row['supplierPayment'],2) }}</td>

    <td>{{ number_format($row['totalPayment'],2) }}</td>

    <td>{{ number_format($row['totalPurchase'],2) }}</td>

    <td>{{ number_format($row['totalReturn'],2) }}</td>

</tr>

@endforeach

</tbody>

</table>


</body>
</html>