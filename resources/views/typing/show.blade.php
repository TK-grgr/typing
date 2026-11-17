@extends('layouts.app')

@section('main')

<div class="wrapper">

    <div class="blank"></div>

    <h1>

        {{ $question->title }}

    </h1>

    <div class="typing-area">

        <div id="questionCount">
            1 / 10
        </div>

        <div id="targetText">Spaceキーで開始。</div>

        <input type="text" id="typingInput" autocomplete="off" placeholder="ここに入力してください">
    </div>
</div>
<meta
    name="csrf-token"
    content="{{ csrf_token() }}">

<script>
window.typingData = {
    questionId: {{ $question->id }},
    title: @json($question->title),
    details: @json(
        $question->details
            ->pluck('text')
            ->toArray()
    ),
    saveUrl:"{{ route('typing.saveResult') }}"
};

</script>
<script src="{{ asset('js/typing.js') }}"></script>
@endsection