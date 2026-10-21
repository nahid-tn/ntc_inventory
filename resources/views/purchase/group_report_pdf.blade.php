<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Purchase By Group Report</title>
<style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 12px; 
        margin: 20px;
    }
    table { 
        width: 100%; 
        border-collapse: collapse; 
        margin: 10px 0; 
    }
    table, th, td { 
        border: 1px solid #000; 
    }
    th, td { 
        padding: 8px; 
        text-align: left; 
    }
    .text-center { 
        text-align: center; 
    }
    .text-right { 
        text-align: right; 
    }
    .header { 
        margin-bottom: 20px; 
        border-bottom: 2px solid #333;
        padding-bottom: 10px;
    }
    .positive { 
        color: green; 
    }
    .negative { 
        color: red; 
    }
    .info-box {
        background: #f5f5f5;
        padding: 10px;
        margin: 10px 0;
        border-left: 3px solid #333;
    }
    .summary-box {
        margin-top: 20px;
        padding: 10px;
        background: #f9f9f9;
        border: 1px solid #ddd;
    }
    .no-data {
        text-align: center;
        padding: 30px;
        color: #999;
    }
</style>
</head>
<body>

<div class="header">
    <h2 class="text-center">Purchase By Group Report</h2>
</div>

<div class="info-box">
    <p><strong>Period:</strong> 
        @if(!empty($from_date))
            {{ date('d-m-Y', strtotime($from_date)) }}
        @else
            All Records
        @endif
        to
        @if(!empty($to_date))
            {{ date('d-m-Y', strtotime($to_date)) }}
        @else
            All Records
        @endif
    </p>
    <p><strong>Opening Balance:</strong> {{ number_format($openingBalance ?? 0, 2) }}</p>
</div>

@if(empty($transactions) || count($transactions) == 0)
    <div class="no-data">
        <p>No transactions found in this period.</p>
    </div>
@else
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th class="text-right">Amount (Debit)</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $runningBalance = $openingBalance ?? 0;
                $totalAmount = 0;
            @endphp
            
            @foreach($transactions as $tx)
                @php 
                    $amount = isset($tx['debit']) ? floatval($tx['debit']) : 0;
                    $runningBalance += $amount;
                    $totalAmount += $amount;
                @endphp
                <tr>
                    <td>
                        @if(!empty($tx['date']))
                            {{ date('d-m-Y', strtotime($tx['date'])) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $tx['reference'] ?? '-' }}</td>
                    <td>{{ $tx['supplier'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($amount, 2) }}</td>
                    <td class="text-right {{ $runningBalance >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($runningBalance, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($runningBalance, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
@endif

<div class="summary-box">
    <p><strong>Summary:</strong></p>
    <p>Total Purchases: {{ number_format($grandTotal ?? 0, 2) }}</p>
    <p>Total Paid: {{ number_format($grandPaid ?? 0, 2) }}</p>
    <p>Total Due: {{ number_format($grandDue ?? 0, 2) }}</p>
    <hr>
    <p><strong>Closing Balance: {{ number_format($closingBalance ?? 0, 2) }}</strong></p>
</div>

<p style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
    Generated on: {{ date('d-m-Y h:i:s A') }}
</p>

</body>
</html>