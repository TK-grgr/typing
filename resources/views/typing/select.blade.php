@extends('layouts.app')

@section('main')
    <link rel="stylesheet" href="{{ asset('css/typing.css') }}">
    <script>
        // 共通ファイルを変更せず、選択画面だけの表示設定を適用する。
        document.body.classList.add('typing-select-page');
        document.title = 'タイピングメニュー選択 | TypeUp';
    </script>
    <script src="{{ asset('js/typing-select.js') }}" defer></script>

    <main
        class="typing-select"
        data-typing-select
        data-active-tab="{{ $initialTab }}"
        data-initial-question="{{ $initialQuestion }}"
        aria-labelledby="typing-select-title"
    >
        <h1 class="typing-select__title" id="typing-select-title">タイピングメニュー選択</h1>

        <div class="typing-select__tabs" role="tablist" aria-label="タイピングの種類">
            @foreach ($tabs as $tab)
                <button
                    class="typing-select__tab"
                    id="typing-tab-{{ $tab['id'] }}"
                    type="button"
                    role="tab"
                    data-tab-id="{{ $tab['id'] }}"
                    aria-selected="{{ $tab['id'] === $initialTab ? 'true' : 'false' }}"
                    aria-controls="typing-select-panel"
                    tabindex="{{ $tab['id'] === $initialTab ? '0' : '-1' }}"
                >{{ $tab['label'] }}</button>
            @endforeach
        </div>

        <div
            class="typing-select__panel"
            id="typing-select-panel"
            role="tabpanel"
            aria-labelledby="typing-tab-{{ $initialTab }}"
        >
            <div class="typing-select__choices">
                @foreach ($groups as $type => $group)
                    <section class="typing-select__group" aria-labelledby="typing-heading-{{ $type }}">
                        <h2 class="typing-select__heading" id="typing-heading-{{ $type }}">{{ $group['label'] }}</h2>

                        <div
                            class="typing-select__question-list"
                            role="region"
                            aria-labelledby="typing-heading-{{ $type }}"
                            tabindex="0"
                        >
                            @foreach ($group['items'] as $item)
                                <button
                                    class="typing-select__question"
                                    type="button"
                                    data-question-id="{{ $item['id'] }}"
                                    data-question-mode="{{ $item['mode'] }}"
                                    data-question-type="{{ $type }}"
                                    title="{{ $item['title'] }}{{ $item['playable'] ? '' : '（現在出題できる問題セットではありません）' }}"
                                    aria-pressed="{{ $item['id'] === $initialQuestion ? 'true' : 'false' }}"
                                    @if ($item['mode'] !== $initialTab) hidden @endif
                                    @disabled(!$item['playable'])
                                >{{ $item['title'] }}</button>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="typing-select__divider" aria-hidden="true"></div>

            <div class="typing-select__actions">
                <button class="typing-select__action" type="button">戻る</button>
                <button class="typing-select__action" type="button" data-typing-start data-game-url="{{ route('typing.game') }}" @disabled($initialQuestion === null)>開始</button>
            </div>
        </div>
    </main>
@endsection
