// 正打数・ミス数・経過時間から、正確率・入力速度と4方式のスコアを算出する。
((root) => {
    'use strict';

    // 採点前にモード・種別と計測値を検証し、表示できない成績は null を返す。
    function calculate(play) {
        if (!play || !['game', 'practice'].includes(play.mode) || !['word', 'long'].includes(play.type)) return null;
        const { correct: C, miss: M, elapsedMs, minimumKeystrokes: N } = play;
        if (![C, M, N].every(count => Number.isSafeInteger(count) && count >= 0) || !Number.isFinite(elapsedMs)) return null;

        // C は正打数、M はミス数、N は全問題の最短ローマ字打鍵数（いずれも打鍵）。
        // elapsedMs はミリ秒、T は秒。T が 0 以下なら 0.01 秒を使い、ゼロ除算を防ぐ。
        const seconds = elapsedMs / 1000;
        const T = seconds > 0 ? seconds : 0.01;
        // A は全打鍵に占める正打の割合（0～1）。打鍵がなければ 0 としてゼロ除算を防ぐ。
        const A = C + M === 0 ? 0 : C / (C + M);
        // V は正打数を 1 分あたりに換算した入力速度（文字/分）。
        const V = C / T * 60;

        let score;
        if (play.mode === 'game' && play.type === 'word') {
            // ゲーム×単語。V × 10 × A²。速度点全体に正確率の二乗を掛け、ミスの影響を強く反映する。
            score = V * 10 * A ** 2;
        } else if (play.mode === 'game') {
            // ゲーム×長文。V × 5 + V × 5 × A²。速度による基本点に、正確さに応じた加点を足す。
            score = V * 5 + V * 5 * A ** 2;
        } else if (play.type === 'word') {
            // 実践×単語。(C − M) ÷ T × 1000。正打からミスを差し引いた打鍵数を速度に換算する。
            // ミスが正打を上回った場合も差を成績に反映するため、負の点数を許容する。
            score = (C - M) / T * 1000;
        } else {
            // 実践×長文。V × A × (1 + N ÷ 250)。速度と正確さに、問題セットの長さによる補正を掛ける。
            // N は入力方式に左右されない最短打鍵数。250 打鍵を基準とし、N が 250 のとき補正倍率は 2。
            score = V * A * (1 + N / 250);
        }

        if (!Number.isFinite(V) || !Number.isFinite(score)) return null;
        // 途中では丸めず、最終スコアだけを四捨五入する。負の値も絶対値を丸めてから符号を戻す。
        const roundedScore = Math.sign(score) * Math.round(Math.abs(score)) || 0;
        return { score: roundedScore, accuracy: A, speed: V };
    }

    const api = { calculate };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.TypingScore = api;
})(typeof window !== 'undefined' ? window : globalThis);
