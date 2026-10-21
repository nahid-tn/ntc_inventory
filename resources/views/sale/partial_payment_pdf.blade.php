@php
    // ================= বাংলা কনভার্টার হেল্পার =================
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

    $bnInv = function($invoice) {
        if (empty($invoice)) return '';
        $invoice = str_replace(['INV-', 'inv-', 'Inv-'], '', $invoice);
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return str_replace($en, $bnChars, (string) $invoice);
    };

    $unitMap = [
        'PC'   => 'টি', 'PCS'  => 'টি', 'SET'  => 'সেট', 'KG'   => 'কেজি',
        'KGS'  => 'কেজি', 'BOX'  => 'বক্স', 'DGN'  => 'ডজন', 'JOR'  => 'জোরা',
        'GUR'  => 'গুরুস', 'DRM'  => 'ড্রাম', 'DRAM' => 'ড্রাম', 'PAC'  => 'প্যাকেট',
    ];

    $bnUnit = function($unit) use ($unitMap) {
        if (empty($unit)) return '';
        $key = strtoupper(trim($unit));
        return $unitMap[$key] ?? $unit;
    };
@endphp

<div style="width:100%;">

    <!-- ================= হেডার শুরু ================= -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:10px; border-bottom:2px solid #000; padding-bottom:10px;">
        <tr>
            <!-- বাম দিকের লোগো -->
            <td style="width:20%; vertical-align:middle; text-align:left; border:none;">
                <!-- এখানে আপনার লোগোর সঠিক path বসান -->
                <img src="{{ public_path('images/logo.png') }}" alt="NTC Logo" style="height:60px; width:auto;">
            </td>
            
            <!-- ডান দিকের কোম্পানির তথ্য -->
            <td style="width:80%; vertical-align:middle; text-align:center; border:none; line-height:1.4;">
                <div style="font-size:20px; font-weight:bold; color:#000;">নাফিস ট্রেড কর্পোরেশন</div>
                <div style="font-size:13px; color:#333;">কালিবাড়ী রোড, মনিরামপুর, শাহজাদপুর, সিরাজগঞ্জ</div>
                <div style="font-size:13px; color:#333;">
                    মোবাইল: ০১৭১২৪০৭০৪২, ০১৫৫১৮০৬১০২ | ইমেইল: nahidtn@gmail.com
                </div>
            </td>
        </tr>
    </table>
    <!-- ================= হেডার শেষ ================= -->

    <h3 style="text-align:center; margin:4px 0; font-size:18px; font-weight:bold; line-height:1.2;">আংশিক পেমেন্ট / লেজার বিবরণী</h3>

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

    @php $sl = 1; @endphp
    @php $productTotal = 0; @endphp <!-- ⭐ টোটাল ভেরিয়েবল -->

    <h4 style="margin:8px 0 4px 0; font-size:15px; font-weight:bold; line-height:1.2;"><u>বর্তমান চালানের পণ্যসমূহঃ</u></h4>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">ক্রমিক</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">পণ্যের নাম</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">পরিমাণ</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">একক</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">দর</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $item)
                @php 
                    $line = ($item->qty ?? 0) * ($item->price ?? 0); 
                    $productTotal += $line; // ⭐ যোগফল
                @endphp
                <tr>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bn($sl++, 0) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $item->product->product_name ?? 'N/A' }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item->qty) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bnUnit($item->unit ?? 'PC') }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item->price) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($line) }}</td>
                </tr>
            @endforeach

            @foreach($oldProducts as $item)
                @php $productTotal += $item->total; @endphp <!-- ⭐ পুরাতন পণ্যের টোটাল -->
                <tr>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bn($sl++, 0) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $item->product_name }} (পুরাতন)</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item->qty) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">-</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($item->price) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">(-) {{ $bn($item->total) }}</td>
                </tr>
            @endforeach

            <!-- ⭐ মোট যোগফল এখানে দেখানো হচ্ছে -->
            <tr style="font-weight:bold; background-color:#e9ecef;">
                <td colspan="5" style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">সর্বমোট:</td>
                <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">{{ $bn($productTotal) }}</td>
            </tr>
        </tbody>
    </table>

    <h4 style="margin:8px 0 4px 0; font-size:15px; font-weight:bold; line-height:1.2;"><u>পেমেন্টের সারসংক্ষেপঃ</u></h4>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">বর্তমান চালানের মোট</td>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($netTotal) }}</td>
        </tr>
        <tr>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">
                পরিশোধিত (চালান নং: {{ $bnInv($sale->invoice_no) }})
            </td>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($totalPaid) }}</td>
        </tr>
        <tr>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">বর্তমান বাকি</td>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($currentDue) }}</td>
        </tr>
        <tr>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">পূর্ববর্তী বাকি</td>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($previousDue) }}</td>
        </tr>
        <tr style="font-weight:bold; background-color:#f0f0f0;">
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">সর্বমোট বাকি</td>
            <td style="text-align:right; padding:2px 5px; font-size:14px; line-height:1.15;">{{ $bn($totalSaleDue) }}</td>
        </tr>
    </table>

    <h4 style="margin:8px 0 4px 0; font-size:15px; font-weight:bold; line-height:1.2;"><u>সর্বশেষ ১০টি বিক্রয়ঃ</u></h4>

    @php $sl = 1; @endphp

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">ক্রমিক</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">চালান</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">তারিখ</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">মোট</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">পরিশোধিত</th>
                <th style="border:1px solid #999; padding:2px 5px; font-size:14px; font-weight:bold; line-height:1.15;">বাকি</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lastSalesData as $row)
                <tr @if($row['is_current']) style="background-color:#fff3cd;" @endif>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bn($sl++, 0) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bnInv($row['invoice']) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:center; font-size:14px; line-height:1.15;">{{ $bnDate($row['date']) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($row['total']) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($row['paid']) }}</td>
                    <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px; line-height:1.15;">{{ $bn($row['due']) }}</td>
                </tr>
            @endforeach
            <!-- ⭐ টোটাল যোগফল -->
            <tr style="font-weight:bold; background-color:#e9ecef;">
                <td colspan="3" style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">সর্বমোট:</td>
                <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">{{ $bn($totalSaleAmount) }}</td>
                <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">{{ $bn($totalSalePaid) }}</td>
                <td style="border:1px solid #999; padding:2px 5px; text-align:right; font-size:14px;">{{ $bn($totalSaleDue) }}</td>
            </tr>
        </tbody>
    </table>

</div>