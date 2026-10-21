@php
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

    // ⭐ তারিখ বাংলায় কনভার্ট
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
            'name'    => $item->product->product_name ?? 'N/A',
            'qty'     => $item->qty ?? 0,
            'unit'    => $item->unit ?? 'PC',
            'remarks' => $item->narration ?? '',
        ]);
    }

    foreach ($sale->oldProductPurchases as $purchase) {
        foreach ($purchase->details as $item) {
            $allItems->push([
                'name'    => ($item->oldProduct->product_name ?? 'Old') . ' (পুরাতন)',
                'qty'     => $item->qty ?? 0,
                'unit'    => $item->unit ?? 'KG',
                'remarks' => $item->narration ?? '',
            ]);
        }
    }

    $chunks      = $allItems->chunk($chunkSize);
    $totalChunks = $chunks->count();
    $sl          = 1;
@endphp

@foreach($chunks as $chunkIndex => $chunk)
    @php
        $isFirstPage = ($chunkIndex == 0);
        $isLastPage  = ($chunkIndex == $totalChunks - 1);
    @endphp

    <div style="page-break-inside: avoid;">

        @if($isFirstPage)
            <h3 style="text-align:center; margin:4px 0; font-size:18px; font-weight:bold; line-height:1.2;">চালান কপি</h3>

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
            <h4 style="text-align:center; margin:3px 0; font-size:15px; font-weight:bold; line-height:1.2;">চালান কপি (চলমান)</h4>
            <p style="text-align:center; margin:1px 0; font-size:12px; line-height:1.25;">
                চালান: {{ $sale->invoice_no }} | পৃষ্ঠা {{ $bn($chunkIndex + 1, 0) }} / {{ $bn($totalChunks, 0) }}
            </p>
        @endif

        <table style="width:100%; border-collapse:collapse; margin-top:4px;">
            <thead>
                <tr style="background-color:#f0f0f0;">
                    <th style="border:1px solid #999; padding:2px 5px; width:6%; font-size:14px; font-weight:bold; line-height:1.15;">ক্রমিক</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:39%; font-size:14px; font-weight:bold; line-height:1.15;">পণ্যের নাম</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:15%; font-size:14px; font-weight:bold; line-height:1.15;">পরিমাণ</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:15%; font-size:14px; font-weight:bold; line-height:1.15;">একক</th>
                    <th style="border:1px solid #999; padding:2px 5px; width:25%; font-size:14px; font-weight:bold; line-height:1.15;">মন্তব্য</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $item)
                    <tr>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bn($sl++, 0) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $item['name'] }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item['qty']) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bnUnit($item['unit']) }}</td>
                        <td style="border:1px solid #999; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $item['remarks'] }}</td>
                    </tr>
                @endforeach

                
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
                <tr>
                <td colspan="5" style="border: none; text-align:right; font-size:12px; padding: 25px 0;">
                    পৃষ্ঠা {{ $bn($chunkIndex + 1, 0) }} / {{ $bn($totalChunks, 0) }}
                </td>
            </tr>
            </table>
        @endif

    </div>

    @if(!$isLastPage)
        <pagebreak />
    @endif

@endforeach