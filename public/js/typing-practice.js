// 問題本文と入力文を照合し、最小の編集回数で一致する文字が最も多い経路を選ぶ。
((root) => {
    'use strict';

    const maxLength = 4096;
    const empty = () => ({ correct: 0, typo: 0, omission: 0, extra: 0 });
    const characters = value => Array.from(value.replace(/\r\n?/g, '\n'));

    function compare(text, answer, final = false) {
        const target = characters(text);
        const input = characters(answer);
        // 編集回数を優先して比較し、同じ編集回数なら一致文字が多い経路を選ぶ。
        const weight = target.length + 1;
        const steps = [null];
        let row = Int32Array.from({ length: weight }, (_, i) => i * weight);

        input.forEach((character, j) => {
            const next = new Int32Array(weight);
            const moves = new Uint8Array(weight);
            next[0] = (j + 1) * weight;
            for (let i = 1; i < weight; i++) {
                const diagonal = row[i - 1] + (target[i - 1] === character ? -1 : weight);
                const omission = next[i - 1] + weight;
                const extra = row[i] + weight;
                next[i] = Math.min(diagonal, omission, extra);
                moves[i] = next[i] === diagonal ? 0 : next[i] === omission ? 1 : 2;
            }
            steps.push(moves);
            row = next;
        });

        // 入力中は未入力の末尾を除外し、同点なら長い連続範囲を選ぶ。
        let i = target.length;
        if (!final) {
            for (let prefix = i - 1; prefix >= 0; prefix--) {
                if (row[prefix] < row[i]) i = prefix;
            }
        }

        const counts = empty();
        let j = input.length;
        while (i && j) {
            const move = steps[j][i];
            if (move === 0) {
                counts[target[--i] === input[--j] ? 'correct' : 'typo']++;
            } else if (move === 1) {
                counts.omission++;
                i--;
            } else {
                counts.extra++;
                j--;
            }
        }
        counts.omission += i;
        counts.extra += j;
        return counts;
    }

    const api = { compare, empty, maxLength };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.TypingPractice = api;
})(typeof window !== 'undefined' ? window : globalThis);
