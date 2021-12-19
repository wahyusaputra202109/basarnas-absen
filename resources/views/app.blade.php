<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}"/>
        <link rel="icon" type="image/x-icon" href="favicon.ico"/>
        <title>ABSENSI ONLINE | BASARNAS</title>
    </head>
    <body style="margin:0">
        <div id="app">
            <app></app>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
