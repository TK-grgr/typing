@extends('layouts.app')

@section('main')

<div class="wrapper">

    <h1>問題編集</h1>

    <div class="edit-card">

        <form
            action="{{ route(
                'questions.update',
                $question->id
            ) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">

                <label>
                    タイトル
                </label>

                <input
                    type="text"
                    name="title"
                    value="{{ $question->title }}">

            </div>

            <div class="form-group">

                <label>
                    問題番号
                </label>

                <input
                    type="number"
                    name="no"
                    value="{{ $question->no }}">

            </div>

            <div class="form-group">

                <label>
                    モード
                </label>

                <select name="level">

                    <option
                        value="1"
                        {{ $question->level == 1 ? 'selected' : '' }}>

                        実践

                    </option>

                    <option
                        value="2"
                        {{ $question->level == 2 ? 'selected' : '' }}>

                        ゲーム

                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    問題種別
                </label>

                <select name="type">

                    <option
                        value="1"
                        {{ $question->type == 1 ? 'selected' : '' }}>

                        長文

                    </option>

                    <option
                        value="2"
                        {{ $question->type == 2 ? 'selected' : '' }}>

                        短文

                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    公開状態
                </label>

                <select name="release">

                    <option
                        value="1"
                        {{ $question->release == 1 ? 'selected' : '' }}>

                        公開

                    </option>

                    <option
                        value="0"
                        {{ $question->release == 0 ? 'selected' : '' }}>

                        非公開

                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    問題文
                </label>

                <textarea
                    name="text"
                    rows="12">{{ $text }}</textarea>

            </div>

            <div class="btn-area">

                <a
                    href="{{ route('questions.index') }}"
                    class="back-btn">

                    一覧へ戻る

                </a>

                <button
                    type="submit"
                    class="save-btn">

                    保存

                </button>

            </div>

        </form>

        <div class="danger-box">

            <h3>
                問題削除
            </h3>

            <form
                action="{{ route(
                    'questions.destroy',
                    $question->id
                ) }}"
                method="POST">

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="delete-btn"
                    onclick="return confirm(
                        '本当に削除しますか？'
                    )">

                    問題を削除する

                </button>

            </form>

        </div>

    </div>

</div>

@endsection