@extends('layouts.app')

@section('main')

<div class="wrapper">
    <div class="blank"></div>
    <h1>タイピング結果</h1>
    <div class="result-card">
        <h2>{{ $result->question->title }}</h2>
        <div class="result-row">
            <span class="result-label">スコア</span>
            <span class="result-value">{{ number_format($result->score) }}</span>
        </div>
        <div class="result-row">
            <span class="result-label">正解文字数</span>
            <span class="result-value">{{ $result->correct }}</span>
        </div>
        @if($result->question->level == 2)
        <div class="result-row">
            <span class="result-label">ミス数</span>
            <span class="result-value">{{ $result->miss }}</span>
        </div>
        @endif
        <div class="result-row">
            <span class="result-label">入力時間</span>
            <span class="result-value">{{ $result->input_time }} 秒</span>
        </div>
        <div class="result-row">
            <span class="result-label">プレイ日時</span>
            <span class="result-value">{{ $result->created_at->format('Y/m/d H:i') }}</span>
        </div>
        <div class="btn-area">
        <a href="{{ route('typing.show',$result->question_id) }}"class="save-btn">もう一度挑戦</a>
        <a href="{{ route('typing.index') }}" class="back-btn">問題選択へ戻る</a>
        <a href="{{ route('result.index') }}"class="back-btn">履歴を見る</a>
    </div>
    </div>
</div>
@endsection