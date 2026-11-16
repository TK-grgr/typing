(() => {
    'use strict';

    const screen = document.querySelector('[data-typing-select]');

    if (!screen) {
        return;
    }

    const tabs = Array.from(screen.querySelectorAll('[data-tab-id]'));
    const questions = Array.from(screen.querySelectorAll('[data-question-id]'));
    const panel = screen.querySelector('[role="tabpanel"]');
    let activeTabId = screen.dataset.activeTab;

    // タブの状態と問題の状態は分離し、タブごとに問題を1つ保持する。
    // 初期選択は実践タブのみ。ほかのタブは未選択から開始する。
    const selectedQuestionByTab = new Map(tabs.map((tab) => [
        tab.dataset.tabId,
        tab.dataset.tabId === activeTabId ? screen.dataset.initialQuestion : null,
    ]));

    const renderQuestions = () => {
        const selectedQuestionId = selectedQuestionByTab.get(activeTabId);

        questions.forEach((question) => {
            question.setAttribute('aria-pressed', String(question.dataset.questionId === selectedQuestionId));
        });
    };

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
            selectedQuestionByTab.set(activeTabId, question.dataset.questionId);
            renderQuestions();
        });
    });

    renderQuestions();
})();
