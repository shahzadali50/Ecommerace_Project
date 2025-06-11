<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', subject: app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
            <!-- ✅ Favicon -->
        <link rel="icon" type="image/webp" href="{{ asset('assets/images/favicon.webp') }}" />
         <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="{{ asset('admin/assets/css/customAdmin.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/color.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/customFrontend.css') }}">
        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body>
        @inertia

    </body>
</html>
