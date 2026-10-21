<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyInfoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('company_info')->insert([
            'company_name_english'       => 'Nafis Trade Corporation',
            'company_name_bangla'        => 'নাফিস ট্রেড কর্পোরেশন',
            'company_nick_name_english'  => 'NTC',
            'company_nick_name_bangla'   => 'এনটিসি',
            'email'                      => 'nafistrade@gmail.com',
            'company_address_english'    => 'Kalibari Road (Nearby Shaheen School), Moniramp, Shahzadpur, Sirajganj',
            'company_address_bangla'     => 'কালীবাড়ি রোড (শাহীন স্কুলের নিকটে), মনিরামপুর, শাহজাদপুর, সিরাজগঞ্জ',
            'company_phone_english'      => '01712-407042, 01551-806102',
            'company_phone_bangla'       => '০১৭১২-৪০৭০৪২, ০১৫৫১-৮০৬১০২',
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);
    }
}