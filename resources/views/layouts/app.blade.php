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
    <nav class="nav">
        <div class="nav-logo">
            <a href="{{ route('top') }}">
                <img class="nav-img" src="{{ asset('images/キーボードと上昇矢印のTypeUpロゴ 1.png') }}"alt="TypeUp">
            </a>
        </div>
                <ul class="nav-menu">
                        {{-- <li class="left-nav-text"><a href="">プロフィール</a></li> --}}
                        <li><a href="{{ route('top') }}">ホーム</a></li>
                        <li><a href="">タイピング</a></li>
                        <li><a href="{{ route('Rank.index') }}">ランキング</a></li>
                        <li><a href="">メニュー作成</a></li>
                        <li><a href="{{ route('Profile.index') }}" >プロフィール</a></li>
                        <li><a href="{{ route('logout') }}" 
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                                class="nav-menu-end">
                                    <span class="red-text">ログアウト</span>
                                </a>
                                
                                <!-- ログアウト用の隠しフォーム -->
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                </ul>
            </nav>
    @yield('main')

</body>
</html>