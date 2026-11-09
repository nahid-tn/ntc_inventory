<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->type !== 'admin') {
            abort(403, 'আপনার এই পেজে প্রবেশের অনুমতি নেই।');
        }

        return $next($request);
    }
}