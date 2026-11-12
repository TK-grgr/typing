@extends('layouts.app')

@section('main')

<div class="wrapper">

    <div class="blank"></div>

    <div class="admin-tabs">

        <a
            href="{{ route('admin.index') }}"
            class="admin-tab">
            ユーザー管理
        </a>

        <a
            href="{{ route('questions.index') }}"
            class="admin-tab active">
            タイピングメニュー管理
        </a>

    </div>

    <div class="admin-header">

        <h2>タイピングメニュー管理</h2>

        <a
            href="{{ route('questions.create') }}"
            class="save-btn">

            ＋ 新規作成

        </a>

    </div>

    <div class="admin-sub-header">

        <div>
            登録件数：
            {{ $questions->count() }}件
        </div>

    </div>

    <table class="admin-table">

        <thead>

            <tr>

                <th>タイピング名</th>

                <th>タイピングルール</th>

                <th>公開状態</th>

                <th>作成日</th>

                <th>操作</th>

            </tr>

        </thead>

        <tbody>

            @forelse($questions as $question)

            <tr>

                <td>

                    {{ $question->title }}

                </td>

                <td>

                    <span class="rule-badge">

                        {{ $question->level == 1 ? '実践' : 'ゲーム' }}

                        /

                        {{ $question->type == 1 ? '長文' : '短文' }}

                    </span>

                </td>

                <td>

                    @if($question->release == 1)

                        <span class="enabled">

                            公開中

                        </span>

                    @else

                        <span class="disabled">

                            非公開

                        </span>

                    @endif

                </td>

                <td>

                    {{ $question->created_at->format('Y/m/d') }}

                </td>

                <td>

                    <a
                        href="{{ route(
                            'questions.show',
                            $question->id
                        ) }}"
                        class="edit-btn">

                        編集

                    </a>

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="5">

                    登録されているタイピング問題はありません

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection