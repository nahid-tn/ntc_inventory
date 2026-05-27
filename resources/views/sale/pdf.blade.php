<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">

<style>
@font-face {
    font-family: 'SolaimanLipi';
    src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
}

@page {
    size: A4;
    margin: 10mm 12mm;
}

body {
    font-family: 'SolaimanLipi', sans-serif;
    font-size: 12px;
    margin: 0;
}

/* PAGE */
.page {
    height: 100%;
}

/* EACH HALF */
.copy {
    height: 48%;
}

/* DIVIDER */
.divider {
    border-top: 1px dashed #000;
    margin: 12px 0;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

table, th, td {
    border: .5px solid #999;
}

th, td {
    padding: 3px;
    text-align: center;
    font-size: 11px;
}
</style>
</head>

<body>

<div class="page">

    {{-- TOP --}}
    <div class="copy">
        @include('sale.partials.invoice', ['title' => 'Challan Copy'])
    </div>

    <div class="divider"></div>

    {{-- BOTTOM --}}
    <div class="copy">
        @include('sale.partials.invoice', ['title' => 'Bill Copy'])
    </div>

</div>

</body>
</html>