// 選択画面のモード切り替え、問題セットの選択、ゲーム画面への遷移を担当する。
(() => {
    'use strict';

    const screen = document.querySelector('[data-typing-select]');

    if (!screen) {
        return;
    }

    const tabs = Array.from(screen.querySelectorAll('[data-tab-id]'));
    const questions = Array.from(screen.querySelectorAll('[data-question-id]'));
    const panel = screen.querySelector('[role="tabpanel"]');
    const startButton = screen.querySelector('[data-typing-start]');
    let activeTabId = screen.dataset.activeTab;

    // タブの状態と問題の状態は分離し、タブごとに問題を1つ保持する。
    // 初期選択は実践タブのみ。ほかのタブは未選択から開始する。
    const selectedQuestionByTab = new Map(tabs.map((tab) => [
        tab.dataset.tabId,
        tab.dataset.tabId === activeTabId ? screen.dataset.initialQuestion : null,
    ]));
    const selectedQuestion = () => questions.find(question =>
        !question.disabled && question.dataset.questionMode === activeTabId &&
        question.dataset.questionId === selectedQuestionByTab.get(activeTabId));

    // 現在のモードのセットだけを表示し、出題可能なセットが選ばれたときに開始を有効にする。
    const renderQuestions = () => {
        const selected = selectedQuestion();

        questions.forEach((question) => {
            question.hidden = question.dataset.questionMode !== activeTabId;
            question.setAttribute('aria-pressed', String(question === selected));
        });
        if (startButton) startButton.disabled = !selected;
    };

    // タブを切り替え、そのモードで最後に選んだセットを再表示する。
    const selectTab = (tab) => {
        activeTabId = tab.dataset.tabId;
        screen.dataset.activeTab = activeTabId;

        tabs.forEach((candidate) => {
            const isSelected = candidate === tab;
            candidate.setAttribute('aria-selected', String(isSelected));
            candidate.tabIndex = isSelected ? 0 : -1;
        });

        panel.setAttribute('aria-labelledby', tab.id);
        renderQuestions();
    };

    // 矢印キーは両端で循環し、Home と End は先頭と末尾へ移動する。
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', (event) => {
            let nextIndex;

            switch (event.key) {
                case 'ArrowRight':
                    nextIndex = (index + 1) % tabs.length;
                    break;
                case 'ArrowLeft':
                    nextIndex = (index - 1 + tabs.length) % tabs.length;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = tabs.length - 1;
                    break;
                default:
                    return;
            }

            event.preventDefault();
            selectTab(tabs[nextIndex]);
            tabs[nextIndex].focus();
        });
    });

    questions.forEach((question) => {
        question.addEventListener('click', () => {
            if (question.disabled || question.hidden) return;
            selectedQuestionByTab.set(activeTabId, question.dataset.questionId);
            renderQuestions();
        });
    });

    // 遷移先にはモード、単語・長文の種別、DB上のセットIDを渡す。
    startButton?.addEventListener('click', () => {
        const question = selectedQuestion();
        if (!question) {
            return;
        }

        const url = new URL(startButton.dataset.gameUrl, window.location.origin);
        url.searchParams.set('mode', activeTabId);
        url.searchParams.set('type', question.dataset.questionType);
        url.searchParams.set('set', question.dataset.questionId);
        window.location.assign(url);
    });

    renderQuestions();
})();
