// 正しい文字数または正打数と、ミス数・経過時間から採点する。
((root) => {
    'use strict';

    // 採点前にモード・種別と計測値を検証し、表示できない成績は null を返す。
    function calculate(play) {
        if (!play || !['game', 'practice'].includes(play.mode) || !['word', 'long'].includes(play.type)) return null;
        const { correct: C, miss: M, elapsedMs, minimumKeystrokes: N } = play;
        // ゲームの記録は最短打鍵数も検証する。実践の採点では最短打鍵数を使わない。
        const counts = play.mode === 'game' ? [C, M, N] : [C, M];
        if (!counts.every(count => Number.isSafeInteger(count) && count >= 0) || !Number.isFinite(elapsedMs)) return null;

        // C は正しい文字数（ゲームは正打数）、M はミス数。
        // elapsedMs はミリ秒、T は秒。T が 0 以下なら 0.01 秒を使い、ゼロ除算を防ぐ。
        const seconds = elapsedMs / 1000;
        const T = seconds > 0 ? seconds : 0.01;
        // A は全打鍵に占める正打の割合（0～1）。打鍵がなければ 0 としてゼロ除算を防ぐ。
        const A = C + M === 0 ? 0 : C / (C + M);
        // V は正打数を 1 分あたりに換算した入力速度（文字/分）。
        const V = C / T * 60;

        let score;
        if (play.mode === 'practice' || play.type === 'word') {
            // 実践の短文・長文とゲームの単語。V × 10 × A²。
            score = V * 10 * A ** 2;
        } else {
            // ゲーム×長文。V × 5 + V × 5 × A²。速度による基本点に、正確さに応じた加点を足す。
            score = V * 5 + V * 5 * A ** 2;
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
