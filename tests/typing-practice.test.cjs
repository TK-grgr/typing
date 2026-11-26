// 誤字・脱字・余字の区別と、JavaScriptとPHPの照合結果の一致を検証する。
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const { test } = require('node:test');
const fs = require('node:fs');
const vm = require('node:vm');
const window = {};
vm.runInNewContext(fs.readFileSync(require.resolve('../public/js/typing-practice.js'), 'utf8'), { window });
const compare = (...args) => ({ ...window.TypingPractice.compare(...args) });

const target = '図書館に行く';
const cases = [
    [target, '図書館に効く', { correct: 5, typo: 1, omission: 0, extra: 0 }],
    [target, '図書館行く', { correct: 5, typo: 0, omission: 1, extra: 0 }],
    [target, '図書館にに行く', { correct: 6, typo: 0, omission: 0, extra: 1 }],
    ['Ａ、😀', 'A。😀', { correct: 1, typo: 2, omission: 0, extra: 0 }],
    ['a　 ', 'a ', { correct: 2, typo: 0, omission: 1, extra: 0 }],
    ['a\r\nb', 'a\nb', { correct: 3, typo: 0, omission: 0, extra: 0 }],
];

test('practice aligns complete text predictably and preserves width and punctuation', () => {
    for (const [text, answer, expected] of cases) {
        assert.deepEqual(compare(text, answer, true), expected);
    }
});

test('practice excludes only the unfinished suffix until leaving or finishing a question', () => {
    assert.deepEqual(compare(target, ''), { correct: 0, typo: 0, omission: 0, extra: 0 });
    assert.deepEqual(compare(target, '図書'), { correct: 2, typo: 0, omission: 0, extra: 0 });
    assert.deepEqual(compare(target, '図書', true), { correct: 2, typo: 0, omission: 4, extra: 0 });
    assert.deepEqual(compare(target, '図書に行'), { correct: 4, typo: 0, omission: 1, extra: 0 });
    assert.deepEqual(compare(target, '', true), { correct: 0, typo: 0, omission: 6, extra: 0 });
});

test('PHP and JavaScript produce identical edit counts', () => {
    // 同じ文字が連続する文字列を組み合わせ、複数の照合経路が同点になる場合も比較する。
    const texts = ['', 'あ', 'ああ', 'あい', 'いあ', 'あいあ', 'いああ', 'あいい', 'abc'];
    const comparisons = cases.map(([text, answer]) => [text, answer]);
    for (const text of texts) for (const answer of texts) comparisons.push([text, answer]);
    const result = JSON.parse(execFileSync('php', [require.resolve('./fixtures/typing-scoring-parity.php')], {
        input: JSON.stringify({ plays: [], readings: [], comparisons }), encoding: 'utf8',
    }));
    assert.deepEqual(result.comparisons, comparisons.map(([text, answer]) => compare(text, answer, true)));
});
