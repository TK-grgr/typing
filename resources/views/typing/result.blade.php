@extends('layouts.app')

@section('main')
    <link rel="stylesheet" href="{{ asset('css/typing-result.css') }}">
    <script src="{{ asset('js/typing-score.js') }}" defer></script>
    <script src="{{ asset('js/typing-result.js') }}" defer></script>
    <script>
        // 共通レイアウトは変更せず、結果画面だけの表示設定を適用する。
        document.body.classList.add('typing-result-page');
    </script>

    <main class="typing-result" data-result-mode="{{ $mode }}" data-result-type="{{ $questionType }}" data-result-set="{{ $setId }}"
        data-result-user-id="{{ $playerId }}" aria-labelledby="typing-result-title">
        <section class="typing-result__card" aria-label="今回のタイピング結果">
            <h1 class="typing-result__title" id="typing-result-title">{{ $modeName }}｜{{ $questionTypeName }}｜{{ $setName }}</h1>

            {{-- モード別の項目を並べ、保存済み成績がある場合に値を埋める。 --}}
            <dl class="typing-result__metrics" style="--metric-count: {{ count($metrics) }};">
                @foreach ($metrics as $key => $label)
                    <div class="typing-result__metric" data-metric="{{ $key }}">
                        <dt>{{ $label }}</dt>
                        <dd>―</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- 同じ条件での再プレイと選択画面への戻り先。 --}}
        <nav class="typing-result__actions" aria-label="結果画面の操作">
            <a class="typing-result__action" href="{{ route('typing.game', ['type' => $questionType, 'mode' => $mode, 'set' => $setId]) }}">もう一度</a>
            <a class="typing-result__action" href="{{ route('typing.select') }}">選択画面へ戻る</a>
        </nav>
    </main>
@endsection
