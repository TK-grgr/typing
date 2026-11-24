@extends('layouts.app')

@section('main')
<div class="wrapper">
    <h1>パスワードの再設定</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div style="color: green;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div>
            <label for="password">新しいパスワード</label>
            <input id="password" type="password" name="password" required>
        </div>

        <div>
            <label for="password_confirmation">新しいパスワード（確認）</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <button type="submit">パスワードを更新する</button>
    </form>
</div>
@endsection