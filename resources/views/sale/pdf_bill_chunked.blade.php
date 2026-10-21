@php
    $received  = $sale->receivedPayments->sum('amount');
    $chunkSize = $chunkSize ?? 38;

    $bn = function($num, $decimals = 2) {
        $num = is_numeric($num) ? (float) $num : 0;
        $formatted = number_format($num, $decimals);
        $en = ['0','1','2','3','4','5','6','7','8','9','.',','];
        $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','.','،'];
        return str_replace($en, $bnChars, $formatted);
    };

    $bnPhone = function($num) {
        if (empty($num)) return '';
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return str_replace($en, $bnChars, (string) $num);
    };

    $bnDate = function($date) {
    if (empty($date)) return '';
    $formatted = \Carbon\Carbon::parse($date)->format('d-m-Y');
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bnChars, $formatted);
};

    // ⭐ ইনভয়েস নাম্বার বাংলায় (শুধু সংখ্যা)
    $bnInv = function($invoice) {
    if (empty($invoice)) return '';
    // ⭐ "INV-" বা "inv-" বাদ দিন
    $invoice = str_replace(['INV-', 'inv-', 'Inv-'], '', $invoice);
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($en, $bnChars, (string) $invoice);
};

    // ⭐ Unit বাংলা কনভার্টার
    $unitMap = [
        'PC'   => 'টি',
        'PCS'  => 'টি',
        'SET'  => 'সেট',
        'KG'   => 'কেজি',
        'KGS'  => 'কেজি',
        'BOX'  => 'বক্স',
        'DGN'  => 'ডজন',
        'JOR'  => 'জোরা',
        'GUR'  => 'গুরুস',
        'DRM'  => 'ড্রাম',
        'DRAM' => 'ড্রাম',
        'PAC'  => 'প্যাকেট',
    ];

    $bnUnit = function($unit) use ($unitMap) {
        if (empty($unit)) return '';
        $key = strtoupper(trim($unit));
        return $unitMap[$key] ?? $unit;
    };

    $allItems = collect();

    foreach ($sale->details as $item) {
        $allItems->push([
            'type'  => 'new',
            'name'  => $item->product->product_name ?? 'N/A',
            'qty'   => $item->qty ?? 0,
            'unit'  => $item->unit ?? 'PC',
            'price' => $item->price ?? 0,
            'total' => ($item->qty ?? 0) * ($item->price ?? 0),
        ]);
    }

    foreach ($sale->oldProductPurchases as $purchase) {
        foreach ($purchase->details as $item) {
            $allItems->push([
                'type'  => 'old',
                'name'  => ($item->oldProduct->product_name ?? 'Old') . ' (পুরাতন)',
                'qty'   => $item->qty ?? 0,
                'unit'  => $item->unit ?? 'KG',
                'price' => $item->price ?? 0,
                'total' => -1 * (($item->qty ?? 0) * ($item->price ?? 0)),
            ]);
        }
    }

    $chunks       = $allItems->chunk($chunkSize);
    $totalChunks  = $chunks->count();
    $runningTotal = 0;
    $sl           = 1;
@endphp

@foreach($chunks as $chunkIndex => $chunk)
    @php
        $isFirstPage = ($chunkIndex == 0);
        $isLastPage  = ($chunkIndex == $totalChunks - 1);
        $pageTotal   = $chunk->sum('total');
    @endphp

    <div style="page-break-inside: avoid;">

        @if($isFirstPage)
            <h3 style="text-align:center; margin:4px 0; font-size:18px; font-weight:bold; line-height:1.2;">বিল কপি</h3>
            <table style="width:100%; border-collapse:collapse; margin:4px 0; border:none;">
                <tr>
                    <td style="border:none; padding:1px 0; width:60%; font-size:14px; line-height:1.25;">
                        <strong>চালান নম্বর:</strong> {{ $bnInv($sale->invoice_no) }}
                    </td>
                    <td style="border:none; padding:1px 0; width:40%; font-size:14px; line-height:1.25;">
                        <strong>তারিখ:</strong> {{ $bnDate($sale->sale_date) }}
                    </td>
                </tr>
                <tr>
                    <td style="border:none; padding:1px 0; width:60%; font-size:14px; line-height:1.25;">
                        <strong>ক্রেতা:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}
                    </td>
                    <td style="border:none; padding:1px 0; width:40%; font-size:14px; line-height:1.25;">
                        <strong>মোবাইল:</strong> {{ $bnPhone($sale->customer->mobile ?? '') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; padding:1px 0; font-size:14px; line-height:1.25;">
                        <strong>ঠিকানা:</strong> {{ $sale->customer->address ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        @else
            <h4 style="text-align:center; margin:3px 0; font-size:15px; font-weight:bold; line-height:1.2;">বিল কপি (চলমান)</h4>
            <p style="text-align:left; margin:1px 0; font-size:13px; line-height:1.25;">
                চালান: {{ $bnInv($sale->invoice_no) }}
            </p>
        @endif

        <table style="width:100%; border-collapse:collapse; margin-top:4px;">
            <thead>
                <tr style="background-color:#f0f0f0;">
                    <th style="border:1px solid #999; padding:2px 5px; width:6%; font-size:14px; font-weight:bold; line-height:1.15;">ক্রমিক</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:50%; font-size:14px; font-weight:bold; line-height:1.15;">পণ্যের নাম</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:10%; font-size:14px; font-weight:bold; line-height:1.15;">পরিমাণ</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:10%; font-size:14px; font-weight:bold; line-height:1.15;">একক</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:10%; font-size:14px; font-weight:bold; line-height:1.15;">দর</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:16%; font-size:14px; font-weight:bold; line-height:1.15;">মোট</th>
                </tr>
            </thead>
            <tbody>
                @if(!$isFirstPage && $runningTotal != 0)
                    <tr style="background-color:#fffbe6; font-weight:bold;">
                        <td colspan="5" style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">
                            ⟶ পূর্ববর্তী পৃষ্ঠার সমষ্টি (যোগফল):
                        </td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">
                            {{ $bn($runningTotal) }}
                        </td>
                    </tr>
                @endif

                @foreach($chunk as $item)
                    <tr>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bn($sl++, 0) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $item['name'] }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item['qty']) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bnUnit($item['unit']) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item['price']) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">
                            @if($item['type'] == 'old')
                                (-) {{ $bn(abs($item['total'])) }}
                            @else
                                {{ $bn($item['total']) }}
                            @endif
                        </td>
                    </tr>
                @endforeach

                @php $runningTotal += $pageTotal; @endphp
                <tr style="background-color:#e8f4ff; font-weight:bold;">
                    <td colspan="5" style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">
                        @if($isLastPage) সর্বমোট:
                        @else
                            পৃষ্ঠা {{ $bn($chunkIndex + 1, 0) }} এর সমষ্টি:
                        @endif
                    </td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">
                        {{ $bn($runningTotal) }}
                    </td>
                </tr>

                @if($isLastPage)
                    @php
                        $tax      = $sale->tax_amount      ?? 0;
                        $shipping = $sale->shipping_amount ?? 0;
                        $discount = $sale->discount_amount ?? 0;
                        $netPayable = $runningTotal + $tax + $shipping - $discount;
                        $isNegative = $netPayable < 0;
                        $due        = $isNegative ? 0 : max(0, $netPayable - $received);
                    @endphp

                    @if($tax > 0)
                        <tr>
                            <td colspan="5" style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">কর</td>
                            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($tax) }}</td>
                        </tr>
                    @endif

                    @if($shipping > 0)
                        <tr>
                            <td colspan="5" style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">পরিবহন খরচ</td>
                            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($shipping) }}</td>
                        </tr>
                    @endif

                    @if($discount > 0)
                        <tr>
                            <td colspan="5" style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">ছাড়</td>
                            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">(-) {{ $bn($discount) }}</td>
                        </tr>
                    @endif

                    {{-- <tr>
                        <td colspan="5" style="text-align:right; font-weight:bold; padding:2px 5px; font-size:14px; line-height:1.15;">নিট প্রদেয়</td>
                        <td style="text-align:right; font-weight:bold; padding:2px 5px; font-size:14px; line-height:1.15;">
                            @if($netPayable < 0)
                                <span style="color:red;">(-) {{ $bn(abs($netPayable)) }}</span>
                            @else
                                {{ $bn($netPayable) }}
                            @endif
                        </td>
                    </tr> --}}

                    <tr>
                        <td colspan="5" style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">পরিশোধিত</td>
                        <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($received) }}</td>
                    </tr>

                    @if($due > 0)
                        <tr>
                            <td colspan="5" style="text-align:right; font-weight:bold; padding:2px 5px; font-size:14px; line-height:1.15;">বাকি</td>
                            <td style="text-align:right; font-weight:bold; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($due) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>

        @if($isLastPage)
            @php $user = auth()->user(); @endphp
            <table width="100%" style="margin-top:18px; border:none;">
                <tr>
                    <td width="50%" style="border:none; text-align:center;">
                        <div style="margin-top:22px; border-top:1px solid #000; width:80%; margin:auto;"></div>
                        <p style="margin:2px 0; font-size:12px;">ক্রেতার স্বাক্ষর</p>
                    </td>
                    <td width="50%" style="border:none; text-align:center;">
                        @if($user && $user->signature)
                            @php $path = storage_path('app/public/'.$user->signature); @endphp
                            @if(file_exists($path))
                                <img src="file://{{ $path }}" style="height:35px; display:block; margin:0 auto 2px auto;">
                            @endif
                        @endif
                        <div style="border-top:1px solid #000; width:80%; margin:auto;"></div>
                        <p style="margin:2px 0; font-size:12px;">বিক্রেতার স্বাক্ষর</p>
                    </td>
                </tr>
            </table>
        @endif
    </div>

    @if(!$isLastPage)
        <pagebreak />
    @endif

@endforeach