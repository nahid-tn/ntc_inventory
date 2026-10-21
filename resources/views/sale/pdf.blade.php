@php
    $received  = $sale->receivedPayments->sum('amount');
    $printBoth = $printBoth ?? false;
@endphp

{{-- ================= Challan Copy ================= --}}
@if($printBoth)
<div>

    <h3 style="text-align:center; margin:5px 0;">Challan Copy</h3>

    {{-- Invoice Info — ৩ Row Layout --}}
    <table style="width:100%; border-collapse:collapse; margin:4px 0; border:none;">
        {{-- Row 1: Invoice | Date --}}
        <tr>
            <td style="border:none; padding:1px 0; width:60%;">
                <strong>Invoice:</strong> {{ $sale->invoice_no }}
            </td>
            <td style="border:none; padding:1px 0; width:40%;">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}
            </td>
        </tr>

        {{-- Row 2: Customer | Mobile --}}
        <tr>
            <td style="border:none; padding:1px 0; width:60%;">
                <strong>Customer:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}
            </td>
            <td style="border:none; padding:1px 0; width:40%;">
                <strong>Mobile:</strong> {{ $sale->customer->mobile ?? 'N/A' }}
            </td>
        </tr>

        {{-- Row 3: Address --}}
        <tr>
            <td colspan="2" style="border:none; padding:1px 0;">
                <strong>Address:</strong> {{ $sale->customer->address ?? 'N/A' }}
            </td>
        </tr>
    </table>

    @php $sl = 1; @endphp

    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th style="border:1px solid #999; padding:4px; width:5%;">SL</th>
                <th style="border:1px solid #999; padding:4px; width:40%;">Product</th>
                <th style="border:1px solid #999; padding:4px; width:15%;">Qty</th>
                <th style="border:1px solid #999; padding:4px; width:15%;">Unit</th>
                <th style="border:1px solid #999; padding:4px; width:25%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->details as $item)
                <tr>
                    <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $sl++ }}</td>
                    <td style="border:1px solid #999; padding:4px;">{{ $item->product->product_name ?? 'N/A' }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:right;">{{ number_format($item->qty, 2) }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $item->unit ?? 'PC' }}</td>
                    <td style="border:1px solid #999; padding:4px;">{{ $item->narration ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="border:1px solid #999; padding:4px; text-align:center;">No products found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php $user = auth()->user(); @endphp
    <table width="100%" style="margin-top:20px; border:none;">
        <tr>
            <td width="50%" style="border:none; text-align:center;">
                <div style="margin-top:25px; border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:10px;">Customer Signature</p>
            </td>
            <td width="50%" style="border:none; text-align:center;">
                @if($user && $user->signature)
                    @php $path = storage_path('app/public/'.$user->signature); @endphp
                    @if(file_exists($path))
                        <img src="file://{{ $path }}" style="height:35px; display:block; margin:0 auto 3px auto;">
                    @endif
                @endif
                <div style="border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:10px;">Seller Signature</p>
            </td>
        </tr>
    </table>

</div>

<pagebreak />
@endif

{{-- ================= Bill Copy ================= --}}
<div>

    <h3 style="text-align:center; margin:5px 0;">Bill Copy</h3>

    {{-- Invoice Info — ৩ Row Layout --}}
    <table style="width:100%; border-collapse:collapse; margin:4px 0; border:none;">
        {{-- Row 1: Invoice | Date --}}
        <tr>
            <td style="border:none; padding:1px 0; width:60%;">
                <strong>Invoice:</strong> {{ $sale->invoice_no }}
            </td>
            <td style="border:none; padding:1px 0; width:40%;">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}
            </td>
        </tr>

        {{-- Row 2: Customer | Mobile --}}
        <tr>
            <td style="border:none; padding:1px 0; width:60%;">
                <strong>Customer:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}
            </td>
            <td style="border:none; padding:1px 0; width:40%;">
                <strong>Mobile:</strong> {{ $sale->customer->mobile ?? 'N/A' }}
            </td>
        </tr>

        {{-- Row 3: Address --}}
        <tr>
            <td colspan="2" style="border:none; padding:1px 0;">
                <strong>Address:</strong> {{ $sale->customer->address ?? 'N/A' }}
            </td>
        </tr>
    </table>

    @php
        $sl = 1;
        $newTotal = 0;
        $oldTotal = 0;
    @endphp

    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
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
            @forelse($sale->details as $item)
                @php
                    $line = ($item->qty ?? 0) * ($item->price ?? 0);
                    $newTotal += $line;
                @endphp
                <tr>
                    <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $sl++ }}</td>
                    <td style="border:1px solid #999; padding:4px;">{{ $item->product->product_name ?? 'N/A' }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:right;">{{ number_format($item->qty, 2) }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $item->unit ?? 'PC' }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:right;">{{ number_format($item->price, 2) }}</td>
                    <td style="border:1px solid #999; padding:4px; text-align:right;">{{ number_format($line, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border:1px solid #999; padding:4px; text-align:center;">No products found</td>
                </tr>
            @endforelse

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
                        <td style="border:1px solid #999; padding:4px;">{{ $item->oldProduct->product_name ?? '' }} (Old)</td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">{{ $qty }}</td>
                        <td style="border:1px solid #999; padding:4px; text-align:center;">{{ $item->unit ?? 'KG' }}</td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">{{ number_format($price, 2) }}</td>
                        <td style="border:1px solid #999; padding:4px; text-align:right;">(-) {{ number_format($line, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach

            @php
                $tax      = $sale->tax_amount      ?? 0;
                $shipping = $sale->shipping_amount ?? 0;
                $discount = $sale->discount_amount ?? 0;

                $netPayable = ($newTotal + $tax + $shipping) - ($oldTotal + $discount);
                $isNegative = $netPayable < 0;
                $due        = $isNegative ? 0 : max(0, $netPayable - $received);
            @endphp

            @if($discount > 0)
                <tr>
                    <td colspan="5" style="text-align:right;">Discount</td>
                    <td style="text-align:right;">(-) {{ number_format($discount, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="5" style="text-align:right; font-weight:bold;">Net Payable</td>
                <td style="text-align:right; font-weight:bold;">
                    @if($netPayable < 0)
                        <span style="color:red;">(-) {{ number_format(abs($netPayable), 2) }}</span>
                    @else
                        {{ number_format($netPayable, 2) }}
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="5" style="text-align:right;">Paid</td>
                <td style="text-align:right;">{{ number_format($received, 2) }}</td>
            </tr>

            @if($due > 0)
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:bold;">Due</td>
                    <td style="text-align:right; font-weight:bold;">{{ number_format($due, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php $user = auth()->user(); @endphp
    <table width="100%" style="margin-top:20px; border:none;">
        <tr>
            <td width="50%" style="border:none; text-align:center;">
                <div style="margin-top:25px; border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:10px;">Customer Signature</p>
            </td>
            <td width="50%" style="border:none; text-align:center;">
                @if($user && $user->signature)
                    @php $path = storage_path('app/public/'.$user->signature); @endphp
                    @if(file_exists($path))
                        <img src="file://{{ $path }}" style="height:35px; display:block; margin:0 auto 3px auto;">
                    @endif
                @endif
                <div style="border-top:1px solid #000; width:80%; margin:auto;"></div>
                <p style="margin:3px 0; font-size:10px;">Seller Signature</p>
            </td>
        </tr>
    </table>

</div>