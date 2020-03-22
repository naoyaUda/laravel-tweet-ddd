<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <x-partial.shim/>
    @yield('head')
    @yield('title')
</head>
<body>
    <x-partial.header/>
    <x-partial.alert/>
    @yield('content')
    @yield('foot')
    <x-partial.footer/>

    <script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>
