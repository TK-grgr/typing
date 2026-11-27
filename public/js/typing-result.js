// プレイ直後の結果画面に、保存済みの成績と今回の順位を表示する。
(() => {
    'use strict';

    const root = document.querySelector('.typing-result[data-result-mode]');
    if (!root || !window.TypingScore) return;

    // 再読み込みでも同じ成績を読めるよう、タブ内の sessionStorage を参照する。
    let play;
    try {
        play = JSON.parse(window.sessionStorage.getItem('typing.playResult'));
    } catch {
        return;
    }
    // ログインユーザーと出題条件を照合し、別ユーザーや別セットの成績は表示しない。
    const { resultUserId, resultMode, resultType, resultSet } = root.dataset;
    if (!resultUserId || play?.userId !== resultUserId) return;
    if (play?.mode !== resultMode || play.type !== resultType || play.setId !== resultSet) return;
    // 実践の内訳と合計を照合し、内訳を持たない旧方式の結果を新しい項目で表示しない。
    const practice = resultMode === 'practice';
    const errorKeys = ['typo', 'omission', 'extra'];
    if (practice && (!play.counts || play.counts.correct !== play.correct ||
        !errorKeys.every(key => Number.isSafeInteger(play.counts[key]) && play.counts[key] >= 0) ||
        errorKeys.reduce((sum, key) => sum + play.counts[key], 0) !== play.miss)) return;

    const result = window.TypingScore.calculate(play);
    if (!result) return;

    const seconds = Math.floor(Math.max(0, play.elapsedMs) / 1000);
    const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
    const integer = new Intl.NumberFormat('ja-JP');
    const decimal = new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    // スコアはサーバーの保存値を優先し、順位は保存時の値を表示する。順位がなければ「―」にする。
    const values = {
        score: integer.format(Number.isInteger(play.score) ? play.score : result.score),
        correct: `${integer.format(play.correct)} ${practice ? '文字' : '打鍵'}`,
        miss: `${integer.format(play.miss)} 回`,
        elapsed: `${minutes}:${String(seconds % 60).padStart(2, '0')}`,
        accuracy: `${decimal.format(result.accuracy * 100)}%`,
        speed: `${decimal.format(result.speed)} 文字/分`,
        rank: Number.isInteger(play.rank) && play.rank > 0 ? `${integer.format(play.rank)} 位` : '―',
    };
    if (practice) {
        for (const key of errorKeys) values[key] = `${integer.format(play.counts[key])} 文字`;
    }
    for (const [metric, value] of Object.entries(values)) {
        const cell = root.querySelector(`[data-metric="${metric}"] dd`);
        if (cell) cell.textContent = value;
    }
})();
