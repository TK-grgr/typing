@extends('layouts.app')
@section('main')
<div class="wrapper">
    <div class="blank"></div>
    <h1>
        {{ $question->title }}
    </h1>
    <h2 id="missCount">ミス:0</h2>
    <div class="typing-area">
        <div id="questionCount">
            0 / {{ $question->details->count() }}
        </div>
        <div id="targetText">
            Spaceキーで開始
        </div>
        <div id="romanText">
        </div>
    </div>
</div>
<meta name="csrf-token"content="{{ csrf_token() }}">
<script>
window.typingData = {
    questionId: {{ $question->id }},
    details: @json($question->details),saveUrl:"{{ route('typing.saveResult') }}"};
</script>
<script src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/game.js') }}"></script>

@endsection