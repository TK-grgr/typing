@extends('layouts.app')

@section('main')

<div class="wrapper">

    <h1>ユーザー管理</h1>

    <div class="edit-card">

        <div class="edit-title">

            編集中のユーザー

            {{ $user->name }}

            /

            {{ $user->email }}

        </div>

        <h2>ユーザー詳細・編集</h2>

        <form
            action="{{ route('admin.update', $user->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="form-row">

                <div class="form-group">

                    <label>
                        ユーザー名
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ $user->name }}">

                </div>

                <div class="form-group">

                    <label>
                        メールアドレス
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ $user->email }}">

                </div>

            </div>

            <div class="form-row">

                <div class="form-group">

                    <label>
                        権限
                    </label>

                    <select name="role">

                        <option
                            value="1"
                            {{ $user->role == 1 ? 'selected' : '' }}>
                            一般
                        </option>

                        <option
                            value="2"
                            {{ $user->role == 2 ? 'selected' : '' }}>
                            管理者
                        </option>

                    </select>

                </div>

            </div>

            <div class="btn-area">

                <a
                    href="{{ route('admin.index') }}"
                    class="back-btn">

                    一覧へ戻る

                </a>

                <button
                    type="submit"
                    class="save-btn">

                    変更を保存

                </button>

            </div>

        </form>

        <div class="danger-box">

            <h3>
                ユーザー削除
            </h3>

            <form
                action="{{ route('admin.destroy', $user->id) }}"
                method="POST">

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="delete-btn"
                    onclick="return confirm('本当に削除しますか？')">

                    ユーザーを削除する

                </button>

            </form>

        </div>

    </div>

</div>

@endsection