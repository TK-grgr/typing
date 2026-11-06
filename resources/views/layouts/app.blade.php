<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
{{-- リセットCSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
<link href="{{ asset('css/style.css') }}" rel="stylesheet">
<title>TypeUp</title>
</head>
<body>
    <nav>
        <img src="{{ asset('images/キーボードと上昇矢印のTypeUpロゴ 1.png') }}">
        <ul>
            <li><a href="">タイピング</a></li>
            <li><a href="">ランキング</a></li>
            <li><a href="">メニュー作成</a></li>
            <li><a href="">プロフィール</a></li>
        </ul>
    </nav>

    @yield('main')

</body>
</html>