@extends('layouts.app')

@section('main')
<div class="wrapper">

    <div class="blank"></div>

    <h1>タイピングメニュー選択</h1>
    <div class="typing-tabs">
    @foreach($tabs as $tab)
        <a
        class="typing-tab
        {{ ($level == $tab['level'])
        ? 'active'
        : '' }}"
        href="{{ route('typing.index',[
            'level' => $tab['level']
        ]) }}">
            {{ $tab['label'] }} 
        </a>
        @endforeach
    </div>
    <div class="question-flex">
        <div class="question-list">
            <h2>短文</h2>
            <div class="question-grid">
            @foreach($questions_short as $question)

                <a href="{{ route('typing.show',$question->id) }}"
                    class="question-card">
                    <div class="question-title">
                        {{ $question->title }}
                    </div>
                    <div class="question-info">
                        <span class="rule-badge">
                            {{ $question->level == 1
                                ? '実践'
                                : 'ゲーム'
                            }}
                        </span>
                        <span class="rule-badge">
                            {{ $question->type == 1
                                ? '長文'
                                : '短文'
                            }}
                        </span>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
        <div class="question-list">
            <h2>長文</h2>
            <div class="question-grid">
            @foreach($questions_long as $question)

                <a href="{{ route('typing.show',$question->id) }}"
                    class="question-card">
                    <div class="question-title">
                        {{ $question->title }}
                    </div>
                    <div class="question-info">
                        <span class="rule-badge">
                            {{ $question->level == 1
                                ? '実践'
                                : 'ゲーム'
                            }}
                        </span>
                        <span class="rule-badge">
                            {{ $question->type == 1
                                ? '長文'
                                : '短文'
                            }}
                        </span>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>
</div>
@endsection