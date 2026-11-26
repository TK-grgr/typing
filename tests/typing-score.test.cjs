// 4条件の採点式と境界値を検証し、PHPの保存用スコアと比較する。
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const { test } = require('node:test');
const vm = require('node:vm');

const window = {};
vm.runInNewContext(fs.readFileSync(require.resolve('../public/js/typing-score.js'), 'utf8'), { window });
const { calculate } = window.TypingScore;
const base = { mode: 'game', type: 'word', correct: 90, miss: 30, elapsedMs: 30_000, minimumKeystrokes: 125 };

for (const [mode, type, score] of [
    ['game', 'word', 1013], ['game', 'long', 1406],
    ['practice', 'word', 1013], ['practice', 'long', 1013],
]) {
    test(`score: ${mode} × ${type}`, () => {
        const result = calculate({ ...base, mode, type });
        assert.equal(result.accuracy, 0.75);
        assert.equal(result.speed, 180);
        assert.equal(result.score, score);
    });
}

test('perfect accuracy and an uncapped score', () => {
    const result = calculate({ ...base, miss: 0, elapsedMs: 1 });
    assert.equal(result.accuracy, 1);
    assert.equal(result.score, 54_000_000);
});

test('practice never gives a negative score and rounds at the end', () => {
    assert.equal(calculate({ ...base, mode: 'practice', correct: 1, miss: 2, elapsedMs: 16_000 }).score, 4);
});

test('no keystrokes produce zero accuracy, speed and score', () => {
    const result = calculate({ ...base, correct: 0, miss: 0 });
    assert.equal(result.accuracy, 0);
    assert.equal(result.speed, 0);
    assert.equal(result.score, 0);
});

test('zero or negative elapsed time uses 0.01 seconds', () => {
    for (const elapsedMs of [0, -100]) {
        const result = calculate({ ...base, correct: 1, miss: 0, elapsedMs });
        assert.equal(result.speed, 6000);
        assert.equal(result.score, 60_000);
    }
});

test('milliseconds and unrounded accuracy are used until the final rounding', () => {
    const result = calculate({ ...base, correct: 7, miss: 2, elapsedMs: 1234 });
    assert.equal(result.accuracy, 7 / 9);
    assert.equal(result.speed, 7 / 1.234 * 60);
    assert.equal(result.score, 2059);
});

test('practice long does not use the shortest keystroke count', () => {
    const play = { ...base, mode: 'practice', type: 'long' };
    assert.equal(calculate({ ...play, minimumKeystrokes: undefined }).score, 1013);
    assert.equal(calculate({ ...play, minimumKeystrokes: 250 }).score, 1013);
});

test('invalid or incomplete results cannot produce metrics', () => {
    for (const play of [null, {}, { ...base, correct: '90' }, { ...base, miss: -1 },
        { ...base, mode: 'invalid' }, { ...base, minimumKeystrokes: 1.5 },
        { ...base, elapsedMs: Infinity }, { ...base, elapsedMs: NaN },
        { ...base, elapsedMs: 1e-308 }, { ...base, elapsedMs: undefined }]) {
        assert.equal(calculate(play), null);
    }
});

test('PHP matches JavaScript scoring and shortest keystroke rules', () => {
    vm.runInNewContext(fs.readFileSync(require.resolve('../public/js/typing-romaji.js'), 'utf8'), { window });
    const kana = Array.from('あいうえおかきくけこがぎぐげごさしすせそざじずぜぞたちつてとだぢづでどなにぬねのはひふへほばびぶべぼぱぴぷぺぽまみむめもやゆよらりるれろわをんっぁぃぅぇぉゃゅょ');
    const readings = ['ガッコウ', 'シンブン', 'こんにちは。', 'だいじょうぶ！', 'てぃー', 'あした はれ', 'っNN'];
    for (const first of kana) for (const second of kana) readings.push(first + second, 'っ' + first + second);
    const plays = [];
    for (const mode of ['game', 'practice']) for (const type of ['word', 'long']) {
        for (const [correct, miss, elapsedMs, minimumKeystrokes] of [
            [90, 30, 30_000, 125], [1, 2, 16_000, 250], [2, 1, 16_000, 0], [0, 0, 0, 0],
            [1, 0, -1, 400], [7, 2, 1234, 137], [90, 0, 1, 250], [213, 16, 29_999.5, 162],
        ]) plays.push({ mode, type, correct, miss, elapsedMs, minimumKeystrokes });
    }
    const result = JSON.parse(execFileSync('php', [require.resolve('./fixtures/typing-scoring-parity.php')], {
        input: JSON.stringify({ plays, readings }), encoding: 'utf8',
    }));
    assert.deepEqual(result.scores, plays.map(play => calculate(play).score));
    readings.forEach((reading, index) => assert.equal(result.minimums[index], window.TypingRomaji.minimumLength(reading), reading));
});
