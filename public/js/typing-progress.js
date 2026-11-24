// sessionStorage にゲームの進行を保存し、F5時に復元できる状態かを確認する。
(() => {
    'use strict';

    const storageKey = 'typing.gameProgress';
    const isCount = value => Number.isSafeInteger(value) && value >= 0;
    // 成績保存の応答を検証する。順位を取得できなかった場合の null も有効とする。
    const validReceipt = value => Number.isSafeInteger(value?.resultId) && value.resultId > 0 &&
        Number.isInteger(value.score) && (value.rank === null || (isCount(value.rank) && value.rank > 0));

    // 再読み込みに限り、履歴のプレイトークンとユーザー・セット・問題データが一致する記録を読む。
    function read(identity, compiled, now) {
        try {
            if (performance.getEntriesByType('navigation')[0]?.type !== 'reload') return null;
            const saved = JSON.parse(window.sessionStorage.getItem(storageKey));
            if (!saved || saved.version !== 1 || typeof saved.attempt !== 'string' ||
                saved.attempt !== window.history.state?.typingAttempt ||
                Object.entries(identity).some(([key, value]) => saved.identity?.[key] !== value)) return null;

            // 出題順の重複、問題番号と履歴の不一致、開始・終了時刻や保存状態の矛盾を検出する。
            const { order, questionIndex, completedInputs, typed, correct, miss, startedAt, elapsedMs, saveState, receipt } = saved;
            const total = compiled.length;
            if (!Array.isArray(order) || order.length !== total || new Set(order).size !== total ||
                !order.every(index => isCount(index) && index < total) || !isCount(questionIndex) || questionIndex > total ||
                !Array.isArray(completedInputs) || completedInputs.length !== questionIndex ||
                typeof typed !== 'string' || !isCount(correct) || !isCount(miss)) return null;

            const finished = questionIndex === total;
            if (startedAt === null) {
                if (questionIndex !== 0 || typed !== '' || correct !== 0 || miss !== 0) return null;
            } else if (!Number.isFinite(startedAt) || startedAt < 0 || startedAt > now) {
                return null;
            }
            if (finished) {
                if (typed !== '' || !isCount(elapsedMs) || elapsedMs > Math.floor(now - startedAt) ||
                    !['pending', 'failed', 'saved'].includes(saveState) || (saveState === 'saved' && !validReceipt(receipt))) return null;
            } else if (saveState !== 'idle' || elapsedMs !== null) {
                return null;
            }

            // 入力済みのキーを判定器に直接再生する。候補の分岐も再現でき、UI の正打数には加算されない。
            let state;
            let replayed = 0;
            const inputs = finished ? completedInputs : [...completedInputs, typed];
            for (const [index, input] of inputs.entries()) {
                if (typeof input !== 'string') return null;
                state = compiled[order[index]].create();
                for (const key of input) {
                    if (state.complete || !state.push(key)) return null;
                }
                if (state.complete !== (index < questionIndex)) return null;
                replayed += input.length;
            }
            return replayed === correct ? { ...saved, state } : null;
        } catch {
            return null;
        }
    }

    // 保存形式にバージョンを付け、タブ内の進行記録を上書きする。
    function write(progress) {
        try {
            window.sessionStorage.setItem(storageKey, JSON.stringify({ version: 1, ...progress }));
        } catch {
            // 保存容量を超えた場合などは、古い時点へ巻き戻らないよう記録を破棄する。
            clear();
        }
    }

    // ゲームの進行記録だけを破棄し、結果画面用の成績データは残す。
    function clear() {
        try {
            window.sessionStorage.removeItem(storageKey);
        } catch {
            // ストレージへのアクセスが制限されていても選択画面へ戻れる。
        }
    }

    window.TypingProgress = { read, write, clear, validReceipt };
})();
