@php
    $received = $paid ?? 0;
@endphp

<div class="invoice-wrapper" style="width:100%;">

    {{-- ================= FIXED HEADER (প্রতি পেজে রিপিট) ================= --}}
    <div class="print-header">
        <div style="text-align:center;">
            <h2 style="margin:0; font-size:18px;">Nafis Trade Corporation</h2>
            <p style="margin:2px 0; font-size:10px;">
                Kalibari Road (Nearby Shaheen School), Moniramp, Shahzadpur, Sirajganj<br>
                Mobile: 01712-407042, 01551-806102
            </p>
            <h3 style="margin:4px 0 0 0; font-size:13px;">{{ $title }}</h3>
        </div>
    </div>

    {{-- ================= FIXED FOOTER (প্রতি পেজে রিপিট) ================= --}}
    <div class="print-footer">
        Software Generated Report — {{ $title }} | Nafis Trade Corporation | Mobile: 01712-407042
    </div>

    {{-- ================= BODY CONTENT ================= --}}
    <div class="invoice-body">

        {{-- INFO --}}
        <p style="margin:2px 0;">
            <strong>Invoice:</strong> {{ $sale->invoice_no }} <br>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}
        </p>
        <p style="margin:2px 0;">
            <strong>Customer:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}
        </p>
        <p style="margin:2px 0;">
            <strong>Mobile:</strong> {{ $sale->customer->mobile ?? 'N/A' }} |
            <strong>Address:</strong> {{ $sale->customer->address ?? 'N/A' }}
        </p>

        @php
            $sl       = 1;
            $newTotal = 0;
            $oldTotal = 0;
            $received = $sale->receivedPayments ? $sale->receivedPayments->sum('amount') : 0;
            $paid     = $received;
            $isBill   = ($title == 'Bill Copy');
        @endphp

        {{-- ================= PRODUCTS TABLE ================= --}}
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background-color:#f0f0f0;">
                    <th style="border:1px solid #999; padding:4px; width:5%;">SL</th>
                    <th style="border:1px solid #999; padding:4px; width:35%;">Product</th>
                    <th style="border:1px solid #999; padding:4px; width:10%;">Qty</th>
                    <th style="border:1px solid #999; padding:4px; width:10%;">Unit</th>
                    <th style="border:1px solid #999; padding:4px; width:20%;">Price</th>
                    <th style="border:1px solid #999; padding:4px; width:20%;">Total</th>
                </tr>
            </thead>

            <tbody>

                {{-- NEW PRODUCTS --}}
                @forelse($sale->details as $item)
                    @php
                        $line = ($item->qty ?? 0) * ($item->price ?? 0);
                        $newTotal += $line;
                    @endphp
                    <tr>
                        <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $sl++ }}</td>
                        <td style="border:1px solid #999; padding:4px; text-align:left;">
                            {{ $item->product->product_name ?? 'N/A' }}
                        </td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">
                            {{ number_format($item->qty, 2) }}
                        </td>
                        <td style="border:1px solid #999; padding:4px; text-align:center;">
                            {{ $item->unit ?? 'PC' }}
                        </td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">
                            {{ $isBill ? number_format($item->price, 2) : '-' }}
                        </td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">
                            {{ $isBill ? number_format($line, 2) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="border:1px solid #999; padding:4px; text-align:center;">
                            No products found
                        </td>
                    </tr>
                @endforelse

                {{-- OLD PRODUCTS --}}
                @foreach($sale->oldProductPurchases as $purchase)
                    @foreach($purchase->details as $item)
                        @php
                            $qty   = $item->qty   ?? 0;
                            $price = $item->price ?? 0;
                            $line  = $qty * $price;
                            $oldTotal += $line;
                        @endphp
                        <tr>
                            <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $sl++ }}</td>
                            <td style="border:1px solid #999; padding:4px; text-align:left;">
                                {{ $item->oldProduct->product_name ?? '' }} (Old)
                            </td>
                            <td style="border:1px solid #999; padding:4px; text-align:right;">{{ $qty }}</td>
                            <td style="border:1px solid #999; padding:4px; text-align:center;">
                                {{ $item->unit ?? 'KG' }}
                            </td>
                            <td style="border:1px solid #999; padding:4px; text-align:right;">
                                {{ $isBill ? number_format($price, 2) : '-' }}
                            </td>
                            <td style="border:1px solid #999; padding:4px; text-align:right;">
                                (-) {{ $isBill ? number_format($line, 2) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- TOTALS (ONLY BILL COPY) --}}
                @if($isBill)
                    @php
                        $tax      = $sale->tax_amount      ?? 0;
                        $shipping = $sale->shipping_amount ?? 0;
                        $discount = $sale->discount_amount ?? 0;

                        $netPayable = ($newTotal + $tax + $shipping) - ($oldTotal + $discount);
                        $isNegative = $netPayable < 0;
                        $due        = $isNegative ? 0 : max(0, $netPayable - $paid);
                    @endphp

                    @if($discount > 0)
                        <tr>
                            <td colspan="5" style="text-align:right;">Discount</td>
                            <td style="text-align:right;">(-) {{ number_format($discount, 2) }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="5" style="text-align:right;">Net Payable</td>
                        <td style="text-align:right;">
                            @if($netPayable < 0)
                                <span style="color:red;">(-) {{ number_format(abs($netPayable), 2) }}</span>
                            @else
                                {{ number_format($netPayable, 2) }}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" style="text-align:right;">Paid</td>
                        <td style="text-align:right;">{{ number_format($paid, 2) }}</td>
                    </tr>

                    @if($due > 0)
                        <tr>
                            <td colspan="5" style="text-align:right;">Due</td>
                            <td style="text-align:right;">{{ number_format($due, 2) }}</td>
                        </tr>
                    @endif
                @endif

            </tbody>
        </table>

        {{-- ================= SIGNATURE ================= --}}
        @php $user = auth()->user(); @endphp

        <table width="100%" style="margin-top:20px; border:none;">
            <tr>
                <td width="50%" style="border:none; text-align:center;">
                    <div style="margin-top:25px; border-top:1px solid #000; width:80%; margin:auto;"></div>
                    <p style="margin:3px 0; font-size:11px;">Customer Signature</p>
                </td>

                <td width="50%" style="border:none; text-align:center;">
                    @if($user && $user->signature)
                        @php $path = storage_path('app/public/'.$user->signature); @endphp
                        @if(file_exists($path))
                            <img src="file://{{ $path }}"
                                 style="height:35px; display:block; margin:0 auto 3px auto;">
                        @endif
                    @endif
                    <div style="border-top:1px solid #000; width:80%; margin:auto;"></div>
                    <p style="margin:3px 0; font-size:11px;">Seller Signature</p>
                </td>
            </tr>
        </table>

    </div>{{-- /.invoice-body --}}

</div>{{-- /.invoice-wrapper --}}