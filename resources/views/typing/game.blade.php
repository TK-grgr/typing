@extends('layouts.app')

@section('main')
    <link rel="stylesheet" href="{{ asset('css/typing-game.css') }}">
    <script src="{{ asset('js/typing-romaji.js') }}" defer></script>
    <script src="{{ asset('js/typing-progress.js') }}" defer></script>
    <script src="{{ asset('js/typing-game.js') }}" defer></script>
    <script>
        // 共通レイアウトを変更せず、この画面だけに専用CSSを適用する。
        document.body.classList.add('typing-game-page');
    </script>

    @php
        // 日本語配列のキー位置と表示ラベルを定義する。
        // 座標は1330×286の表示領域を基準にし、CSSで画面幅に合わせて縮小する。
        $keyRows = [
            ['keys' => ['半角\n全角', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '^', '¥', 'Back\nSpace'], 'x' => 0, 'y' => 0],
            ['keys' => ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', '@', '['], 'x' => 120, 'y' => 74],
            ['keys' => ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', ';', ':', ']'], 'x' => 174, 'y' => 149],
            ['keys' => ['Z', 'X', 'C', 'V', 'B', 'N', 'M', ',', '.', '/', 'ろ'], 'x' => 208, 'y' => 224],
        ];
        $wideKeys = [
            ['key' => 'Tab', 'label' => 'Tab', 'x' => 0, 'y' => 74, 'width' => 80],
            ['key' => 'Caps Lock', 'label' => 'Caps\nLock', 'x' => 0, 'y' => 149, 'width' => 109],
            ['key' => 'Left Shift', 'label' => 'Shift', 'x' => 0, 'y' => 224, 'width' => 135],
            ['key' => 'Right Shift', 'label' => 'Shift', 'x' => 1194, 'y' => 224, 'width' => 135],
        ];
    @endphp

    <main class="typing-game" data-mode="{{ $mode }}" data-question-type="{{ $questionType }}" data-question-set="{{ $setId }}" aria-labelledby="typing-game-mode"
        data-questions="{{ json_encode($questions, JSON_THROW_ON_ERROR) }}" data-result-url="{{ $resultUrl }}"
        data-save-url="{{ route('typing.result.store') }}" data-resume-url="{{ route('typing.game.resume') }}"
        data-attempt="{{ $attempt }}" data-player-id="{{ $playerId }}" data-question-revision="{{ $questionRevision }}" data-csrf-token="{{ csrf_token() }}">
        <div class="typing-game__status">
            <p class="typing-game__mode" id="typing-game-mode">{{ $modeName }}</p>
            <p class="typing-game__clock">経過時間 <span data-typing-elapsed>00:00</span></p>
        </div>

        <section class="typing-game__question" aria-label="タイピングの問題">
            <p class="typing-game__text" data-typing-start>SPACEでスタート</p>
            <h1 class="typing-game__text" id="typing-game-question" data-typing-text hidden></h1>
            <p class="typing-game__kana" data-typing-kana hidden></p>
            <p class="typing-game__romaji" lang="en" data-typing-romaji hidden></p>
            <p role="status" data-typing-save-status hidden></p>
        </section>

        <figure class="typing-game__keyboard" role="img" aria-label="日本語配列を参考にした仮想キーボード。キーは空欄の表示専用です。">
            <div class="typing-game__key-layout" aria-hidden="true">
                @foreach ($keyRows as $row)
                    @foreach ($row['keys'] as $index => $key)
                        <span
                            class="typing-game__key"
                            data-key="{{ $key === '半角\n全角' ? '半角／全角' : ($key === 'Back\nSpace' ? 'Backspace' : $key) }}"
                            style="--key-left: {{ ($row['x'] + $index * 88) / 1330 * 100 }}%; --key-top: {{ $row['y'] / 286 * 100 }}%; --key-width: {{ 68 / 1330 * 100 }}%; --key-height: {{ 62 / 286 * 100 }}%;"
                        >{{ str_replace('\\n', "\n", $key) }}</span>
                    @endforeach
                @endforeach

                @foreach ($wideKeys as $key)
                    <span
                        class="typing-game__key"
                        data-key="{{ $key['key'] }}"
                        style="--key-left: {{ $key['x'] / 1330 * 100 }}%; --key-top: {{ $key['y'] / 286 * 100 }}%; --key-width: {{ $key['width'] / 1330 * 100 }}%; --key-height: {{ 62 / 286 * 100 }}%;"
                    >{{ str_replace('\\n', "\n", $key['label']) }}</span>
                @endforeach

                <span class="typing-game__key typing-game__key--enter" data-key="Enter">
                    <span class="typing-game__key-label">Enter</span>
                    <svg viewBox="0 0 128 138" preserveAspectRatio="none" focusable="false">
                        <path d="M 0.5 0.5 H 127.5 V 137.5 H 30.5 V 51.5 H 0.5 Z" fill="none" stroke="currentColor" vector-effect="non-scaling-stroke" />
                    </svg>
                </span>
            </div>
            @if ($mode === 'practice')
                <p class="typing-game__keyboard-input" data-typing-keyboard-input aria-hidden="true" hidden><span></span></p>
            @endif
        </figure>
        <nav class="typing-game__actions" aria-label="ゲーム操作">
            <a href="{{ $selectUrl }}" data-typing-exit>途中終了</a>
            <button type="button" data-typing-save-retry hidden>成績を再送する</button>
        </nav>
    </main>
@endsection
