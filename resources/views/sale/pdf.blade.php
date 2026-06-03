<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">

<title>Sale Invoice</title>

<style>
body {
    font-family: solaimanlipi;
    font-size: 12px;
    margin: 0;
    padding: 0;
}

.page {
    width: 100%;
}

.copy {
    page-break-after: auto;
}

.divider {
    border-top: 1px dashed #000;
    margin: 10px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #999;
    padding: 4px;
    font-size: 11px;
}
</style>
</head>

<body>

@php
    $received = $sale->receivedPayments->sum('amount');
@endphp

{{-- ===== TOP COPY ===== --}}
@include('sale.partials.invoice', [
    'sale' => $sale,
    'title' => 'Challan Copy',
    'isBill' => false,
    'paid' => $received
])

<div class="divider"></div>

{{-- ===== BOTTOM COPY ===== --}}
@include('sale.partials.invoice', [
    'sale' => $sale,
    'title' => 'Bill Copy',
    'isBill' => true,
    'paid' => $received
])

</body>
</html>