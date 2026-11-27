// ローマ字の代替入力、途中状態、最短打鍵数、長い入力での挙動を検証する。
const assert = require('node:assert/strict');
global.window = {};
require('../public/js/typing-romaji.js');
const romaji = window.TypingRomaji;
const K = (...points) => String.fromCodePoint(...points);
const tests = [
  [K(0x3057), ['si', 'shi']], [K(0x3061), ['ti', 'chi']], [K(0x3064), ['tu', 'tsu']],
  [K(0x3075), ['hu', 'fu']], [K(0x3057,0x3083), ['sya', 'sha']], [K(0x3058), ['zi', 'ji']],
  [K(0x3057,0x3093,0x3076,0x3093), ['sinbun','shinbun','shinnbunn']],
  [K(0x304b,0x3093,0x3044), ['kanni',"kan'i"]], [K(0x3053,0x3093,0x306b,0x3061,0x306f), ['konnichiha','konnitiha']],
  [K(0x307b,0x3093), ['hon','honn']], [K(0x304c,0x3063,0x3053,0x3046), ['gakkou','gaxtukou']],
  [K(0x307e,0x3063,0x3061,0x3083), ['maccha','mattya']], [K(0x3057,0x3083,0x3057,0x3093), ['syasin','shashin']],
  [K(0x304d,0x3087,0x3046), ['kyou']], [K(0x304d,0x3063,0x3066), ['kitte']],
];
let count = 0;
const failures = [];
const check = (label, fn) => { count++; try { fn(); } catch (error) { failures.push(`${label}: ${error.message}`); } };
for (const [kana, spellings] of tests) for (const spelling of spellings) {
  check(`${kana} accepts ${spelling}`, () => assert.equal(romaji.accepts(kana, spelling), true));
}
for (const [kana, spelling] of [[K(0x3057),'su'],[K(0x307b,0x3093),'honnn'],[K(0x304d,0x3063,0x3066),'kite'],[K(0x3057,0x3093,0x3076,0x3093),'shinbunx']]) {
  check(`${kana} rejects ${spelling}`, () => assert.equal(romaji.accepts(kana, spelling), false));
}
check('minimum length for hon', () => assert.equal(romaji.minimumLength(K(0x307b,0x3093)), 3));
check('terminal n accepted', () => assert.equal(romaji.accepts(K(0x307b,0x3093), 'hon'), true));
const long = K(...Array(100).fill([0x3057,0x3093,0x3076,0x3093]).flat());
const start = performance.now(); romaji.minimumLength(long); const elapsed = performance.now() - start;
check('long text performance', () => assert.ok(elapsed < 1000, `(${elapsed}ms)`));
for (const [kana, spelling, previews] of [
  ['としょかん', 'tosyokann', ['t', 'と', 'とs', 'とsy', 'としょ', 'としょk', 'としょか', 'としょかん', 'としょかん']],
  ['としょかん', 'toshokan', ['t', 'と', 'とs', 'とsh', 'としょ', 'としょk', 'としょか', 'としょかん']],
  ['かんい', 'kanni', ['k', 'か', 'かn', 'かん', 'かんい']],
  ['かんい', "kan'i", ['k', 'か', 'かn', 'かん', 'かんい']],
  ['しんぶん', 'shinbun', ['s', 'sh', 'し', 'しn', 'しんb', 'しんぶ', 'しんぶん']],
  ['がっこう', 'gakkou', ['g', 'が', 'がk', 'がっk', 'がっこ', 'がっこう']],
  ['がっこう', 'gaxtukou', ['g', 'が', 'がx', 'がxt', 'がっ', 'がっk', 'がっこ', 'がっこう']],
  ['まっちゃ', 'maccha', ['m', 'ま', 'まc', 'まっc', 'まっch', 'まっちゃ']],
  ['まっちゃ', 'matcha', ['m', 'ま', 'まt', 'まっc', 'まっch', 'まっちゃ']],
  ['きゃ', 'kya', ['k', 'ky', 'きゃ']],
  ['きゃ', 'kixya', ['k', 'き', 'きx', 'きxy', 'きゃ']],
  ['カナ７、。', 'kana7,.', ['k', 'か', 'かn', 'かな', 'かな7', 'かな7、', 'かな7、。']],
]) {
  check(`${kana}: display while typing ${spelling}`, () => {
    const input = romaji.compile(kana).create();
    assert.equal(input.displayInput, '');
    for (const [index, key] of [...spelling].entries()) {
      assert.equal(input.push(key), true);
      assert.equal(input.displayInput, previews[index], `after ${spelling.slice(0, index + 1)}`);
    }
  });
}
if (failures.length) console.error(failures.join('\n'));
console.log(`${count - failures.length}/${count} passed; ${failures.length} failed; long text ${elapsed.toFixed(1)}ms`);
if (failures.length) process.exitCode = 1;
// 有効な途中入力と、問題を最後まで入力した完全一致を区別して検証する。
for (const [kana, spellings] of tests) for (const spelling of spellings) {
  assert.equal(romaji.matches(kana, spelling), true, `${kana}: ${spelling}`);
}
assert.equal(romaji.matches('きって','kite'),false);
assert.equal(romaji.matches('まっちゃ','matccha'),false);
assert.equal(romaji.matches('こんにちは','konichiha'),false);
assert.equal(romaji.matches('し','s'),false);
const session=romaji.compile('こんにちは').create();
assert.equal(session.push('x'),false);
assert.equal(session.typed,'');
for(const key of 'konnichiha') assert.equal(session.push(key),true);
assert.equal(session.complete,true);
assert.equal(session.remaining,'');
assert.equal(romaji.minimumLength(long),600);
const longSession=romaji.compile(long).create();
for(const key of 'sinbun'.repeat(100)) assert.equal(longSession.push(key),true);
assert.equal(longSession.complete,true);
console.log('Additional full-match, state and long-input assertions passed');
