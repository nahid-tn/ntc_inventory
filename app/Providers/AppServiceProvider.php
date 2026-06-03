<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // mPDF এর জন্য কোনো ServiceProvider রেজিস্টার করা লাগে না
        // এটা সরাসরি new Mpdf() দিয়ে ইউজ হয়
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination Bootstrap 5 ইউজ করবে
        Paginator::useBootstrapFive(); // Laravel 12 এ এটা বেস্ট
        
        // আগে DomPDF এর কনফিগ ছিল, এখন সব ডিলিট
        // mPDF এর কনফিগ Controller এই করা হয়, এখানে লাগে না
    }
}