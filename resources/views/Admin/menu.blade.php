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
            href="{{ route('admin.menu') }}"
            class="admin-tab active">
            タイピングメニュー管理
        </a>

    </div>

    <h2>タイピングメニュー管理</h2>

    <div class="admin-header">

        <span>
            ソート：
            作成日
        </span>

        <span>
            {{ $questions->count() }}件
        </span>

    </div>

    <table class="admin-table">

        <tr>
            <th>タイピング名</th>
            <th>作成者</th>
            <th>タイピングルール</th>
            <th>作成日時</th>
            <th>現在</th>
            <th>操作</th>
        </tr>

        @foreach($questions as $question)

        <tr>

            <td>
                {{ $question->title }}
            </td>

            <td>
                {{ $question->user->name ?? '不明' }}
            </td>

            <td>

                <span class="rule-badge">

                    {{ $question->level == 1 ? '実践' : 'ゲーム' }}

                    /

                    {{ $question->type == 1 ? '長文' : '短文' }}

                </span>

            </td>

            <td>
                {{ $question->created_at->format('Y/m/d') }}
            </td>

            <td>

                @if($question->release == 1)

                    <span class="enabled">
                        有効
                    </span>

                @else

                    <span class="disabled">
                        無効
                    </span>

                @endif

            </td>

            <td>

                {{-- <a
                    href="{{ route(
                        'question.show',
                        $question->id
                    ) }}"
                    class="edit-btn">

                    編集

                </a> --}}
                編集
            </td>

        </tr>

        @endforeach

    </table>

</div>

@endsection