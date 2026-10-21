<?php
// app/Models/CompanyInfo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanyInfo extends Model
{
    protected $table = 'company_info';

    protected $fillable = [
        'company_name_english',
        'company_name_bangla',
        'company_nick_name_english',
        'company_nick_name_bangla',
        'logo',
        'email',
        'company_address_english',
        'company_address_bangla',
        'company_phone_english',
        'company_phone_bangla',
    ];

    /**
     * ক্যাশ সহ প্রথম রেকর্ড পাওয়া (সবচেয়ে বেশি ব্যবহৃত)
     */
    public static function getInfo()
    {
        return Cache::rememberForever('company_info', function () {
            return self::first();
        });
    }

    /**
     * ক্যাশ ক্লিয়ার করা (আপডেটের পর কল করতে হবে)
     */
    public static function clearCache()
    {
        Cache::forget('company_info');
    }
}