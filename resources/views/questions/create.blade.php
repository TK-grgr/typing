@extends('layouts.app')

@section('main')

<div class="wrapper">

    <h1>タイピング問題作成</h1>

    <div class="edit-card">

        <form
            action="{{ route('questions.store') }}"
            method="POST">

            @csrf

            <div class="form-group">

                <label>
                    タイトル
                </label>

                <input
                    type="text"
                    name="title"
                    required>

            </div>

            <div class="form-group">

                <label>
                    問題番号
                </label>

                <input
                    type="number"
                    name="no"
                    required>

            </div>

            <div class="form-group">

                <label>
                    モード
                </label>

                <select name="level">

                    <option value="1">
                        実践
                    </option>

                    <option value="2">
                        ゲーム
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    問題種別
                </label>

                <select name="type">

                    <option value="1">
                        長文
                    </option>

                    <option value="2">
                        短文
                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>
                    公開状態
                </label>

                <select name="release">

                    <option value="1">
                        公開
                    </option>

                    <option value="0">
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
                    rows="10"
                    required></textarea>

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

                    作成

                </button>

            </div>

        </form>

    </div>

</div>

@endsection