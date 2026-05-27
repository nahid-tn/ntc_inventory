<div style="width:100%;">

    {{-- TITLE --}}
    <h3 style="text-align:center; margin:3px 0;">{{ $title }}</h3>

    {{-- HEADER --}}
    <div style="text-align:center;">
        <h2 style="margin:0;">Nafis Trade Corporation</h2>
        <p style="margin:2px 0; font-size:10px;">
            Kalibari Road (Nearby Shaheen School), Moniramp, Shahzadpur, Sirajganj<br>
            Mobile: 01712-407042, 01551-806102
        </p>
        <hr>
    </div>

    {{-- INFO --}}
    <p style="margin:2px 0;">
        <strong>Invoice:</strong> {{ $sale->invoice_no }} |
        <strong>Date:</strong> {{ $sale->sale_date->format('d-m-Y') }}
    </p>
    <p style="margin:2px 0;"><strong>Customer:</strong> {{ $sale->customer->customer_name ?? '' }}</p>
    <p style="margin:2px 0;"><strong>Mobile:</strong> {{ $sale->customer->mobile ?? '' }} | <strong>Address:</strong> {{ $sale->customer->address ?? '' }}</p>
    @php
        $sl = 1;
        $newTotal = 0;
        $oldTotal = 0;
        $received = $sale->receivedPayments->sum('amount');
        $paid     = ($sale->paid_amount ?? 0) + $received;
    @endphp

    {{-- PRODUCTS --}}
    <table>
        <thead>
        <tr>
            <th>SL</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>

        {{-- NEW PRODUCTS --}}
        @foreach($sale->details as $item)
            @php
                $line = $item->qty * $item->price;
                $newTotal += $line;
            @endphp
            <tr>
                <td>{{ $sl++ }}</td>
                <td>{{ $item->product->product_name ?? '' }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ $title=='Bill Copy' ? number_format($item->price,2) : '' }}</td>
                <td class="text-right">{{ $title=='Bill Copy' ? number_format($line,2) : '' }}</td>
            </tr>
        @endforeach

        {{-- OLD PRODUCTS (DEDUCTION) --}}
        @foreach($sale->oldProductPurchases as $purchase)
            @foreach($purchase->details as $i)
                @php
                    $line = $i->qty * $i->price;
                    $oldTotal += $line;
                @endphp
                <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ $i->oldProduct->product_name ?? '' }}</td>
                    <td>{{ $i->qty }}</td>
                    <td>{{ $i->unit }}</td>
                    <td class="text-right">{{ $title=='Bill Copy' ? number_format($i->price,2) : '' }}</td>
                    <td class="text-right">{{ $title=='Bill Copy' ? '( - ) '.number_format($line,2) : '' }}</td>
                </tr>
            @endforeach
        @endforeach

        {{-- TOTALS (ONLY BILL COPY) --}}
        @if($title == 'Bill Copy')
            @php
                $discount   = $sale->discount_amount ?? 0;
                $netPayable = $newTotal - $oldTotal - $discount;
                $due        = $netPayable - $paid;
            @endphp

            @if($discount > 0)
            <tr>
                <td colspan="5" align="right">Discount (-)</td>
                <td class="text-right">( - ) {{ number_format($discount,2) }}</td>
            </tr>
            @endif

            <tr>
                <td colspan="5" align="right"><strong>Net Payable</strong></td>
                <td class="text-right"><strong>{{ number_format($netPayable,2) }}</strong></td>
            </tr>

            <tr>
                <td colspan="5" align="right">Paid</td>
                <td class="text-right" style="color:green;">{{ number_format($paid,2) }}</td>
            </tr>

            @if($due > 0)
            <tr>
                <td colspan="5" align="right">Due</td>
                <td class="text-right">{{ number_format($due,2) }}</td>
            </tr>
            @endif
        @endif

        </tbody>
    </table>

    {{-- SIGNATURE --}}
    <table width="100%" style="margin-top:20px; border:none;">
        <tr>
            <td width="50%" style="border:none; text-align:center; height:100px;">
                <div style="margin-top:30px; border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:11px;">Customer Signature</p>
            </td>

            <td width="50%" style="border:none; text-align:center;">
                @if(auth()->check() && auth()->user()->signature)
                    <img src="{{ public_path(auth()->user()->signature) }}"
                         style="height:35px; display:block; margin:0 auto 3px auto;">
                @endif
                <div style="border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:11px;">Seller Signature</p>
            </td>
        </tr>
    </table>

</div>