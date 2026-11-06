@extends('layouts.sub-app') 
@section('main') 

        {{-- ログインボタンが画面外に行ってしまい触れ　づらいのでいコメントアウト --}}
        {{-- 新規登録も画面外へ出ているので画像サイズ要変更 --}}
        {{-- <img src="{{ asset('images/情報教育委員会｜ロゴ (1).png') }}"> --}}
    <div class="wrapper">
        <div class="blank"></div>
        <div class="center-item">
            <img src="{{ asset('images/キーボードと上昇矢印のTypeUpロゴ 1.png') }}">
        </div>
        <div class="blank"></div>
        <div class="login-form">
            <h2>ログイン</h2>
            <form action="{{ route('login_store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>メールアドレス</label><br>
                    <input class="form-control" type="email" name="email"  placeholder="メールアドレスを入力">
                </div>
                <div class="form-group">
                    <label>パスワード</label><br>
                    <input class="form-control" type="password" name="pass"  placeholder="パスワードを入力">
                    <div class="forget-pass">
                        <a href="{{ route('passChange') }}"><p class="right-item">パスワードをお忘れですか?</p></a>
                    </div>
                </div>
                <button type="submit" class="form-group login-btn">
                    ログイン
                </button>
            </form>
            <div class="sign-up">
                <div class="blank"></div>アカウントをお持ちでない方&emsp;<a href="{{ route('signup.create') }}">新規登録 →</a><div class="blank"></div>
            </div>
        </div>
    </div>
@endsection