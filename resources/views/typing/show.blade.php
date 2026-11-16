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

        <div id="targetText">ここに問題文が出力されます。</div>

        <input type="text" id="typingInput" autocomplete="off" placeholder="ここに入力してください">
    </div>
</div>
<script>
window.typingData = @json($question->details->pluck('text'));
</script>
<script src="{{ asset('js/typing.js') }}"></script>
@endsection