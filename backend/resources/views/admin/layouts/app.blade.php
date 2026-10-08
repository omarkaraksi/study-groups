<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin') · {{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
        @if(app()->getLocale() === 'ar')
            <link href="{{ asset('css/tabler.rtl.min.css') }}" rel="stylesheet">
        @endif
    </head>
    <body>
        <div class="page">
            @include('admin.layouts.navbar')

            <div class="page-wrapper">
                <div class="container-xl">
                    <div class="row g-4">
                        @include('admin.layouts.sidebar')

                        <main class="col-12 col-lg-9 col-xl-10">
                            @yield('content')
                        </main>
                    </div>
                </div>

                @include('admin.layouts.footer')
            </div>
        </div>
    </body>
</html>
