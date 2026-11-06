@extends('layouts.sub-app') 
@section('main') 
<div class="wrapper">
    <div class="blank"></div>
    <div class="blank"></div>
        <div class="login-form">
            <div class="right-item">
                <a href="{{ route('login') }}"><img class="signup-img" src="{{ asset('images/キーボードと上昇矢印のTypeUpロゴ 1.png') }}"></a>
            </div>
            <h1 class="center-item">パスワード再設定</h1>
            {{-- パスワードリセットは未実装 --}}
            <form action="" method="POST">
                @csrf
                <div class="form-group">
                    <label>メールアドレス</label><br>
                    <input class="form-control" type="email" name="email"  placeholder="メールアドレスを入力">
                    @error('email')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="blank"></div>
                <button type="submit" class="form-group login-btn">
                    リセット
                </button>
            </form>
            <div class="sign-up">
                <div class="blank"></div>アカウントをお持ちの方&emsp;<a href="{{ route('login') }}">ログイン →</a><div class="blank"></div>
            </div>
        </div>
    </div>
@endsection