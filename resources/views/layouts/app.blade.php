<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    >

    {{-- Bootstrap 5 CSS (শুধু Bootstrap 5) --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- Font Awesome --}}
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        rel="stylesheet"
    >

    {{-- jQuery UI CSS --}}
    <link
        href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"
        rel="stylesheet"
    >

    {{-- AdminLTE 3 CSS
         সতর্কতা: AdminLTE 3 মূলত Bootstrap 4-এর জন্য তৈরি।
         অন্য পেজে প্রয়োজন থাকলে আপাতত রাখা হয়েছে। --}}
    <link
        href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"
        rel="stylesheet"
    >

    {{-- Vite CSS and JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- jQuery: একবারই লোড হবে --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- jQuery UI: একবারই লোড হবে --}}
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    {{-- Avro Bangla typing --}}
    <script src="https://unpkg.com/avro.js@latest"></script>

    {{--
        AdminLTE 3 JavaScript এখানে বাদ দেওয়া হয়েছে।
        এটি Bootstrap 4-নির্ভর এবং Bootstrap 5-এর সঙ্গে
        সংঘর্ষ তৈরি করতে পারে।
    --}}
</head>

<body class="font-sans antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        {{-- Navigation Menu --}}
        @include('layouts.navigation')

        {{-- Page Heading --}}
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- Page Content --}}
        <main>
            {{ $slot }}
        </main>

    </div>

    {{-- Bootstrap 5 JavaScript Bundle: একবারই লোড হবে --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/alpine.min.js') }}"></script>
</body>
</html>