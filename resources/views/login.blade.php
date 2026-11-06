@extends('layouts.app') 
@section('main') 
<div id="login">
        <img src="{{ asset('images/情報教育委員会｜ロゴ (1).png') }}">
    <div class="wrapper">
        <div class="center-item">
            <img src="{{ asset('images/キーボードと上昇矢印のTypeUpロゴ 1.png') }}">
        </div>
        <div class="login-form">
            <h2>ログイン</h2>
            <form action="{{ route('login_store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>メールアドレス</label><br>
                    <input class="form-control" type="email" name="email">
                </div>
                <div class="form-group">
                    <label>パスワード</label><br>
                    <input class="form-control" type="password" name="password">
                    <a href=""><p class="right-item">パスワードをお忘れですか?</p></a>
                </div>
                <button type="submit" class="form-group login-btn">
                    ログイン
                </button>
            </form>
        </div>
    </div>
</div>
@endsection