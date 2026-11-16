<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>タイピングメニュー選択 | TypeUp</title>
    <link rel="stylesheet" href="{{ asset('css/typing.css') }}">
    <script src="{{ asset('js/typing-select.js') }}" defer></script>
</head>
<body class="typing-select-page">
    {{-- 将来の共通ヘッダーはmainの前に組み込める。今回はヘッダーを出力しない。 --}}
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
                @foreach ($groups as $group)
                    <section class="typing-select__group" aria-labelledby="typing-heading-{{ $group['id'] }}">
                        <h2 class="typing-select__heading" id="typing-heading-{{ $group['id'] }}">{{ $group['label'] }}</h2>

                        <div
                            class="typing-select__question-list"
                            role="region"
                            aria-labelledby="typing-heading-{{ $group['id'] }}"
                            tabindex="0"
                        >
                            @foreach ($group['items'] as $item)
                                <button
                                    class="typing-select__question"
                                    type="button"
                                    data-question-id="{{ $item['id'] }}"
                                    aria-label="{{ $group['label'] }}の選択肢 {{ $loop->iteration }}"
                                    aria-pressed="{{ $item['id'] === $initialQuestion ? 'true' : 'false' }}"
                                ></button>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="typing-select__divider" aria-hidden="true"></div>

            <div class="typing-select__actions">
                <button class="typing-select__action" type="button">戻る</button>
                <button class="typing-select__action" type="button">開始</button>
            </div>
        </div>
    </main>
</body>
</html>
