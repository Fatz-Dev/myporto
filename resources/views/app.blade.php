<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>
         <link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpg+xml" />
        <link rel="preconnect" href="https://fonts.bunny.net">
        <meta name="description" content="portfolio and blog of Fatz Dev" />
        <meta name="keywords" content="FatzDev, portfolio, blog" />
        <meta name="author" content="FatzDev" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
