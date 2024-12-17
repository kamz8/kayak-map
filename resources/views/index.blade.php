<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('/favicon-apple.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('/favicon-32x32.png')}}">
    <link rel="icon" href="{{ asset('/favicon.ico') }}" type="image/x-icon">
    <meta name="robots" content="index, follow">
    <title>{{env("APP_NAME")}}</title>
    @vite('resources/js/app.js')
</head>
<body>
<div id="app"></div>
</body>
</html>
