const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

const cwd = path.resolve(__dirname, '..');
const readScripts = (...names) => names.map(name =>
    fs.readFileSync(path.join(cwd, 'public/js', name), 'utf8'));
const scripts = readScripts('typing-romaji.js', 'typing-progress.js', 'typing-game.js');
const resultScripts = readScripts('typing-score.js', 'typing-result.js');
const routes = new Map();
function requestRoute(url, scenario = 'authenticated') {
    const key = `${scenario}:${url.href}`;
    if (!routes.has(key)) {
        const output = execFileSync('php', ['tests/fixtures/typing-route.php', url.href, scenario], { cwd, encoding: 'utf8' });
        routes.set(key, JSON.parse(output));
    }
    return routes.get(key);
}

function renderRoute(url, scenario) {
    const response = requestRoute(url, scenario);
    assert.equal(response.status, 200, url.href);
    return response.html;
}

const decode = value => value.replaceAll('&quot;', '"').replaceAll('&amp;', '&');
const savedResponse = async () => ({ ok: true, json: async () => ({ resultId: 12, score: 314, rank: 2 }) });
function startGame(url, random, session = new Map(), reply = savedResponse, page = {}) {
    const html = renderRoute(url);
    assert.doesNotMatch(html, /結果画面へ（確認用）/);
    assert.match(html, /href="[^"]*\/typing" data-typing-exit>途中終了/);
    const attribute = name => decode(html.match(new RegExp(`data-${name}="([^"]*)"`))[1]);
    const questions = attribute('questions');
    assert.match(html, /data-typing-start>SPACEでスタート/);
    assert.match(html, /data-typing-elapsed>00:00/);
    assert.match(html, /data-typing-text hidden/);
    assert.match(html, /data-typing-kana hidden/);
    assert.match(html, /data-typing-romaji hidden/);
    const text = { hidden: true }, kana = { hidden: true };
    const guide = { hidden: true, replaceChildren(...children) { this.children = children; } };
    const prompt = { hidden: false }, clock = { textContent: '00:00' };
    const saveStatus = { hidden: true };
    const retry = { hidden: true, addEventListener(name, callback) { this[name] = callback; } };
    const exit = { addEventListener(name, callback) { this[name] = callback; } };
    const elements = {
        '[data-typing-text]': text, '[data-typing-kana]': kana, '[data-typing-romaji]': guide,
        '[data-typing-start]': prompt, '[data-typing-elapsed]': clock,
        '[data-typing-save-status]': saveStatus, '[data-typing-save-retry]': retry,
        '[data-typing-exit]': exit,
    };
    const keys = Array.from('ABCDEFGHIJKLMNOPQRSTUVWXYZ,.', key => {
        const classes = new Set();
        return { dataset: { key }, classes, classList: {
            add: name => classes.add(name), remove: (...names) => names.forEach(name => classes.delete(name)),
        } };
    });
    const root = {
        dataset: { questions, resultUrl: attribute('result-url'), saveUrl: attribute('save-url'),
            resumeUrl: attribute('resume-url'), csrfToken: attribute('csrf-token'), attempt: page.attempt ?? attribute('attempt'),
            mode: attribute('mode'), questionType: attribute('question-type'), questionSet: attribute('question-set'),
            playerId: page.playerId ?? attribute('player-id'), questionRevision: page.revision ?? attribute('question-revision') },
        querySelector: selector => elements[selector], querySelectorAll: () => keys,
    };
    const handlers = {}, navigations = [], timers = new Map();
    const intervals = new Map();
    const requests = [];
    const resumeRequests = [];
    let timerId = 0, now = 0;
    const writes = [];
    const history = { state: page.historyState ?? null, replaceState(value) { this.state = value; } };
    const browser = {
        location: { href: url.href, assign: target => navigations.push(target) }, addEventListener() {},
        history,
        sessionStorage: {
            getItem: key => session.get(key) ?? null,
            removeItem: key => session.delete(key),
            setItem(key, value) { if (key === 'typing.playResult') writes.push(key); session.set(key, value); },
        },
    };
    const context = vm.createContext({
        window: browser, URL, Math: Object.assign(Object.create(Math), { random: () => random }),
        performance: { now: () => now, timeOrigin: page.timeOrigin ?? 100_000, getEntriesByType: () => [{ type: page.type ?? 'navigate' }] },
        fetch: (target, options) => {
            if (target === root.dataset.resumeUrl) {
                resumeRequests.push({ target, options });
                return Promise.resolve(page.resumeResponse ?? { ok: true, status: 204 });
            }
            requests.push({ target, options });
            return reply();
        },
        document: {
            querySelector: () => root, createElement: () => ({ style: {} }), createTextNode: value => value,
            addEventListener: (name, handler) => (handlers[name] ??= []).push(handler),
        },
        setTimeout: callback => (timers.set(++timerId, callback), timerId), clearTimeout: id => timers.delete(id),
        setInterval: callback => (intervals.set(++timerId, callback), timerId), clearInterval: id => intervals.delete(id),
    });
    scripts.forEach(script => vm.runInContext(script, context));
    const type = (key, options = {}) => {
        const code = /^[a-z]$/i.test(key) ? `Key${key.toUpperCase()}` : ({ ',': 'Comma', '.': 'Period', ' ': 'Space' })[key] ?? 'Digit9';
        for (const event of ['keydown', 'keyup']) {
            handlers[event].forEach(handler => handler({ key, code, preventDefault() {}, ...options }));
        }
    };
    const advance = ms => { now += ms; };
    const restoreVisibility = () => handlers.visibilitychange.forEach(handler => handler());
    return { questions: JSON.parse(questions), text, kana, guide, prompt, clock, keys, timers, intervals, navigations,
        type, advance, restoreVisibility, data: root.dataset, romaji: browser.TypingRomaji, session, writes, requests,
        resumeRequests, history, exit, retry, saveStatus };
}

const flushPromises = () => new Promise(resolve => setImmediate(resolve));

function loadResult(url, session, scenario) {
    const html = renderRoute(url, scenario);
    assert.match(html, /typing-score\.js[^>]*>[\s\S]*typing-result\.js/);
    const attribute = name => decode(html.match(new RegExp(`data-result-${name}="([^"]*)"`))[1]);
    const cells = Object.fromEntries(Array.from(html.matchAll(/data-metric="(\w+)"[^>]*>\s*<dt>[^<]*<\/dt>\s*<dd>([^<]*)<\/dd>/g),
        ([, key, textContent]) => [key, { textContent }]));
    const root = {
        dataset: { resultMode: attribute('mode'), resultType: attribute('type'), resultSet: attribute('set'), resultUserId: attribute('user-id') },
        querySelector: selector => cells[selector.match(/"(\w+)"/)[1]],
    };
    const context = vm.createContext({
        window: { sessionStorage: { getItem: key => session.get(key) ?? null } },
        document: { querySelector: () => root },
    });
    resultScripts.forEach(script => vm.runInContext(script, context));
    return Object.fromEntries(Object.entries(cells).map(([key, cell]) => [key, cell.textContent]));
}

for (const [mode, type, set, count, expected] of [
    ['practice', 'word', '11', 2, [['新聞', 'しんぶん'], ['学校', 'がっこう']]],
    ['practice', 'long', '12', 3, [['明日は学校に行きます。', 'あしたはがっこうにいきます。']]],
    ['game', 'word', '13', 1, [['情報処理', 'じょうほうしょり']]],
    ['game', 'long', '14', 4, [['青い空を見上げた。', 'あおいそらをみあげた。']]],
]) {
    test(`${mode} × ${type}: shuffle, cumulative counts, completion and replay`, async () => {
        const url = new URL(`http://localhost/typing/game?mode=${mode}&type=${type}&set=${set}`);
        const game = startGame(url, 0);
        assert.equal(game.questions.length, count);
        for (const [text, kana] of expected) {
            assert.deepEqual(game.questions.find(question => question.text === text), { text, kana });
        }
        const order = game.questions.slice(1).concat(game.questions[0]);
        const minimum = game.questions.reduce((sum, q) => sum + game.romaji.minimumLength(q.kana), 0);
        assert.equal(Number(game.data.minimumKeystrokes), minimum);
        game.advance(10_000);
        game.type('9');
        game.type(' ', { repeat: true });
        assert.equal(game.clock.textContent, '00:00');
        assert.equal(game.intervals.size, 0);
        assert.ok(!game.prompt.hidden && game.text.hidden && game.kana.hidden && game.guide.hidden);
        assert.equal(game.data.missCount, '0');
        game.type(' ');
        game.type(' ', { repeat: true });
        assert.ok(game.prompt.hidden && !game.text.hidden && !game.kana.hidden && !game.guide.hidden);
        assert.equal(game.data.correctCount, '0');
        assert.equal(game.data.missCount, '0');
        assert.equal(game.intervals.size, 1);
        game.advance(62_500);
        game.restoreVisibility();
        assert.equal(game.clock.textContent, '01:02');
        let correct = 0;
        order.forEach((question, index) => {
            assert.equal(game.text.textContent, question.text);
            assert.equal(game.kana.textContent, question.kana);
            assert.equal(game.guide.children[0].textContent, '');
            const spelling = game.guide.children[1];
            assert.equal(game.timers.size, 0);
            assert.equal(game.keys.some(key => key.classes.has('is-correct') || key.classes.has('is-wrong')), false);
            assert.ok(game.keys.find(key => key.dataset.key === spelling[0]).classes.has('is-next'));
            game.type(index === 0 ? ' ' : '9');
            assert.equal(game.guide.children[1], spelling);
            assert.equal(Number(game.data.missCount), index + 1);
            assert.equal(game.navigations.length, 0);
            assert.equal(game.requests.length, 0);
            for (const key of spelling.toLowerCase()) game.type(key);
            correct += spelling.length;
            assert.equal(Number(game.data.correctCount), correct);
            assert.equal(game.clock.textContent, '01:02');
        });
        assert.equal(correct, minimum);
        assert.equal(game.data.inputComplete, 'true');
        assert.equal(game.data.elapsedMs, '62500');
        assert.equal(game.intervals.size, 0);
        game.type('9');
        assert.equal(Number(game.data.missCount), order.length);
        assert.equal(game.navigations.length, 0);
        assert.equal(game.requests.length, 1);
        const { target, options } = game.requests[0];
        assert.equal(new URL(target).pathname, '/typing/result');
        assert.equal(options.method, 'POST');
        assert.equal(options.headers['X-CSRF-TOKEN'], game.data.csrfToken);
        assert.ok(game.data.csrfToken);
        assert.deepEqual(JSON.parse(options.body), { attempt: game.data.attempt, correct, miss: order.length, elapsedMs: 62500 });
        await flushPromises();
        assert.equal(game.navigations.length, 1);
        const [result] = game.navigations;
        assert.equal(result.pathname, '/typing/result');
        assert.deepEqual(Object.fromEntries(result.searchParams), { mode, type, set });
        assert.equal(game.writes.length, 1);
        assert.deepEqual(JSON.parse(game.session.get('typing.playResult')), {
            mode, type, setId: set, correct, miss: order.length, elapsedMs: 62500, minimumKeystrokes: minimum,
            resultId: 12, score: 314, rank: 2, userId: game.data.playerId,
        });
        const metrics = loadResult(result, game.session);
        assert.equal(metrics.correct, `${correct} 打鍵`);
        assert.equal(metrics.elapsed, '01:02');
        assert.equal(metrics.speed, `${(correct / 62.5 * 60).toFixed(2)} 文字/分`);
        assert.equal(metrics.accuracy, `${(correct / (correct + order.length) * 100).toFixed(2)}%`);
        assert.equal(metrics.score, '314');
        assert.equal(metrics.miss, mode === 'game' ? `${order.length} 回` : undefined);
        assert.equal(metrics.rank, '2 位');
        assert.deepEqual(loadResult(result, game.session), metrics);
        const replayHref = renderRoute(result).match(/href="([^"]+)"[^>]*>もう一度</)[1];
        const replay = startGame(new URL(decode(replayHref), url), 0.999, game.session);
        assert.equal(replay.clock.textContent, '00:00');
        assert.equal(replay.prompt.hidden, false);
        replay.type(' ');
        assert.equal(replay.session.has('typing.playResult'), false);
        assert.equal(replay.data.elapsedMs, '0');
        assert.equal(replay.text.textContent, game.questions[0].text);
        if (count > 1) assert.notEqual(replay.text.textContent, order[0].text);
        assert.equal(replay.data.correctCount, '0');
        assert.equal(replay.data.missCount, '0');
        assert.equal(replay.data.inputComplete, 'false');
    });
}

test('an interrupted save can be retried using the same attempt', async () => {
    const url = new URL('http://localhost/typing/game?mode=game&type=word&set=13');
    let sent = 0;
    const game = startGame(url, 0, new Map(), async () => ++sent === 1
        ? { ok: false } : { ok: true, json: async () => ({ resultId: 12, score: 314, rank: 2 }) });
    game.type(' ');
    game.advance(5000);
    for (const key of game.guide.children[1].toLowerCase()) game.type(key);
    game.retry.click();
    assert.equal(game.requests.length, 1);
    await flushPromises();
    assert.equal(game.retry.hidden, false);
    assert.equal(game.navigations.length, 0);
    assert.equal(game.writes.length, 0);
    game.retry.click();
    game.retry.click();
    await flushPromises();
    assert.equal(game.requests.length, 2);
    assert.equal(game.requests[0].options.body, game.requests[1].options.body);
    assert.equal(game.navigations.length, 1);
});

test('a saved score without a rank opens the result and keeps the placeholder on reload', async () => {
    const url = new URL('http://localhost/typing/game?mode=game&type=word&set=13');
    const game = startGame(url, 0, new Map(), async () => ({
        ok: true, json: async () => ({ resultId: 12, score: 314, rank: null }),
    }));
    game.type(' ');
    game.advance(5000);
    for (const key of game.guide.children[1].toLowerCase()) game.type(key);
    await flushPromises();
    assert.equal(game.requests.length, 1);
    assert.equal(game.retry.hidden, true);
    assert.equal(game.navigations.length, 1);
    const [result] = game.navigations;
    const metrics = loadResult(result, game.session);
    assert.equal(metrics.score, '314');
    assert.equal(metrics.rank, '―');
    assert.deepEqual(loadResult(result, game.session), metrics);
});

const gameProgressKey = 'typing.gameProgress';
const practiceWords = new URL('http://localhost/typing/game?mode=practice&type=word&set=11');
const readProgress = game => JSON.parse(game.session.get(gameProgressKey));
function reloadGame(game, options = {}) {
    return startGame(options.url ?? practiceWords, 0, game.session, options.reply, {
        type: 'reload', historyState: game.history.state, timeOrigin: 115_250,
        attempt: 'new-token-from-reload', ...options,
    });
}

test('reloading before SPACE keeps the order, original attempt and waiting clock', async () => {
    const game = startGame(practiceWords, 0.999);
    const original = readProgress(game);
    game.advance(9000);
    const restored = reloadGame(game);
    await flushPromises();
    assert.equal(restored.prompt.hidden, false);
    assert.equal(restored.clock.textContent, '00:00');
    assert.equal(restored.requests.length, 0);
    assert.equal(restored.resumeRequests.length, 1);
    assert.equal(new URL(restored.resumeRequests[0].target).pathname, '/typing/game/resume');
    assert.equal(restored.resumeRequests[0].options.headers['X-CSRF-TOKEN'], restored.data.csrfToken);
    assert.deepEqual(JSON.parse(restored.resumeRequests[0].options.body), {
        attempt: original.attempt, currentAttempt: restored.data.attempt,
    });
    restored.type(' ');
    assert.equal(restored.text.textContent, '新聞');
    assert.equal(restored.data.elapsedMs, '0');
    assert.deepEqual(readProgress(restored).order, original.order);
    assert.equal(readProgress(restored).attempt, original.attempt);
});

test('repeated reloads restore alternate input, counts, elapsed time and the last question', async () => {
    let game = startGame(practiceWords, 0.999);
    game.advance(2000);
    game.type(' ');
    game.type('s');
    game.type('9');
    game.advance(6000);
    const original = readProgress(game);
    game = reloadGame(game);
    await flushPromises();
    assert.equal(game.prompt.hidden, true);
    assert.equal(game.text.textContent, '新聞');
    assert.equal(game.guide.children[0].textContent, 'S');
    assert.equal(game.guide.children[1], 'INBUN');
    assert.deepEqual(game.keys.filter(key => key.classes.has('is-next')).map(key => key.dataset.key), ['H', 'I']);
    assert.equal(game.data.correctCount, '1');
    assert.equal(game.data.missCount, '1');
    assert.equal(game.data.elapsedMs, '13250');
    for (const key of 'hinbun') game.type(key);
    assert.equal(game.text.textContent, '学校');
    assert.equal(game.data.correctCount, '7');
    game.type('g');
    game = reloadGame(game, { timeOrigin: 121_500, reply: async () => {
        const play = JSON.parse(game.requests[0].options.body);
        const payload = { plays: [{ ...play, mode: 'practice', type: 'word', minimumKeystrokes: 0 }], readings: [] };
        const output = execFileSync('php', ['tests/fixtures/typing-scoring-parity.php'], {
            cwd, encoding: 'utf8', input: JSON.stringify(payload),
        });
        return { ok: true, json: async () => ({ resultId: 12, score: JSON.parse(output).scores[0], rank: 2 }) };
    } });
    await flushPromises();
    assert.equal(game.text.textContent, '学校');
    assert.equal(game.guide.children[0].textContent, 'G');
    assert.equal(game.data.elapsedMs, '19500');
    assert.equal(game.data.correctCount, '8');
    assert.equal(game.data.missCount, '1');
    assert.equal(game.requests.length, 0);
    assert.equal(readProgress(game).startedAt, original.startedAt);
    assert.deepEqual(readProgress(game).order, original.order);
    for (const key of 'akkou') game.type(key);
    await flushPromises();
    assert.deepEqual(JSON.parse(game.requests[0].options.body), {
        attempt: original.attempt, correct: 13, miss: 1, elapsedMs: 19500,
    });
    const result = game.navigations[0];
    assert.deepEqual(loadResult(result, game.session), loadResult(result, game.session));
    assert.equal(loadResult(result, game.session).score, '615');
});

test('reload during save resends only the frozen result and a saved receipt opens without another POST', async () => {
    const server = new Map();
    let inserts = 0;
    const accept = game => {
        const { attempt } = JSON.parse(game.requests.at(-1).options.body);
        if (!server.has(attempt)) {
            server.set(attempt, { resultId: ++inserts, score: 314, rank: 2 });
        }
        return server.get(attempt);
    };
    // サーバー保存直後、応答がブラウザに届く前の F5 を再現する。
    let game = startGame(practiceWords, 0.999, new Map(), () => { accept(game); return new Promise(() => {}); });
    game.type(' ');
    game.advance(5000);
    for (const key of 'sinbungakkou') game.type(key);
    const originalPost = game.requests[0].options.body;
    assert.equal(readProgress(game).saveState, 'pending');

    game = reloadGame(game, { reply: async () => {
        const receipt = accept(game);
        return { ok: true, json: async () => receipt };
    } });
    await flushPromises();
    assert.equal(game.requests.length, 1);
    assert.equal(game.requests[0].options.body, originalPost);
    assert.equal(game.data.elapsedMs, '5000');
    assert.equal(inserts, 1);
    assert.equal(readProgress(game).saveState, 'saved');
    const result = game.navigations[0];
    assert.equal(loadResult(result, game.session).rank, '2 位');
    const completed = reloadGame(game, { timeOrigin: 130_000 });
    await flushPromises();
    assert.equal(completed.requests.length, 0);
    assert.equal(completed.navigations[0].href, result.href);
    assert.equal(completed.data.elapsedMs, '5000');
    assert.equal(loadResult(result, completed.session).rank, '2 位');
});

test('failed saves remain available for a manual retry after reload', async () => {
    const game = startGame(practiceWords, 0.999, new Map(), async () => ({ ok: false }));
    game.type(' ');
    game.advance(4000);
    for (const key of 'sinbungakkou') game.type(key);
    await flushPromises();
    assert.equal(readProgress(game).saveState, 'failed');
    const restored = reloadGame(game);
    await flushPromises();
    assert.equal(restored.requests.length, 0);
    assert.equal(restored.retry.hidden, false);
    restored.retry.click();
    await flushPromises();
    assert.equal(restored.requests[0].options.body, game.requests[0].options.body);
    assert.equal(restored.navigations.length, 1);
});

test('a fresh navigation, a different identity or invalid progress cannot restore input', async () => {
    const cases = [
        ['normal navigation', { type: 'navigate' }],
        ['different history entry', { historyState: { typingAttempt: 'other-entry' } }],
        ['different user', { playerId: '99' }],
        ['different set', { url: new URL('http://localhost/typing/game?mode=practice&type=word&set=21') }],
        ['changed questions', { revision: 'new-revision' }],
        ['invalid JSON', {}, '{'],
        ['invalid typed input', {}, saved => ({ ...saved, typed: 'z' })],
        ['invalid question order', {}, saved => ({ ...saved, order: [0, 0] })],
        ['expired or rejected token', { resumeResponse: { ok: false, status: 422 } }],
    ];
    for (const [name, options, corrupt] of cases) {
        const game = startGame(practiceWords, 0.999);
        game.type(' ');
        game.type('s');
        if (corrupt) game.session.set(gameProgressKey, typeof corrupt === 'string' ? corrupt : JSON.stringify(corrupt(readProgress(game))));
        const fresh = reloadGame(game, options);
        await flushPromises();
        assert.equal(fresh.prompt.hidden, false, name);
        assert.equal(fresh.clock.textContent, '00:00', name);
        assert.equal(fresh.data.correctCount, '0', name);
        assert.equal(fresh.requests.length, 0, name);
        assert.equal(readProgress(fresh).attempt, fresh.data.attempt, name);
    }
});

test('leaving a game clears only progress and does not submit an unfinished result', async () => {
    const game = startGame(practiceWords, 0.999);
    game.type(' ');
    game.type('s');
    game.session.set('typing.playResult', 'saved-result');
    game.exit.click({ button: 0 });
    assert.equal(game.session.has(gameProgressKey), false);
    assert.equal(game.session.get('typing.playResult'), 'saved-result');
    assert.equal(game.requests.length, 0);
    assert.equal(game.intervals.size, 0);
    const fresh = reloadGame(game);
    await flushPromises();
    assert.equal(fresh.resumeRequests.length, 0);
    assert.equal(fresh.prompt.hidden, false);
});

test('result placeholders stay visible without valid data for the requested set', () => {
    const url = new URL('http://localhost/typing/result?mode=game&type=word&set=13');
    for (const payload of [undefined, '{', '{}', JSON.stringify({ mode: 'game', type: 'word', setId: '14', userId: '1',
        correct: 10, miss: 1, elapsedMs: 1234, minimumKeystrokes: 10 })]) {
        const session = payload === undefined ? new Map() : new Map([['typing.playResult', payload]]);
        assert.ok(Object.values(loadResult(url, session)).every(value => value === '―'));
    }
});

test('same-tab account switching hides saved results and legacy results without an owner', async () => {
    const url = new URL('http://localhost/typing/game?mode=game&type=word&set=13');
    const game = startGame(url, 0);
    game.type(' ');
    game.advance(5000);
    for (const key of game.guide.children[1].toLowerCase()) game.type(key);
    await flushPromises();
    const [result] = game.navigations;
    const ownerMetrics = loadResult(result, game.session);
    assert.equal(ownerMetrics.score, '314');
    assert.equal(ownerMetrics.rank, '2 位');
    assert.deepEqual(loadResult(result, game.session), ownerMetrics);

    const otherMetrics = loadResult(result, game.session, 'different-user');
    assert.deepEqual(Object.keys(otherMetrics), Object.keys(ownerMetrics));
    assert.ok(Object.values(otherMetrics).every(value => value === '―'));

    const { userId, ...legacy } = JSON.parse(game.session.get('typing.playResult'));
    assert.equal(userId, game.data.playerId);
    const legacySession = new Map([['typing.playResult', JSON.stringify(legacy)]]);
    assert.ok(Object.values(loadResult(result, legacySession)).every(value => value === '―'));
});

test('selection groups published titles, sorts by ascending id, and submits the selected DB ID', () => {
    const url = new URL('http://localhost/typing');
    const html = renderRoute(url);
    const mockElement = (dataset, attributes = '') => {
        const listeners = {};
        return {
            dataset, disabled: /\bdisabled\b/.test(attributes), hidden: /\bhidden\b/.test(attributes), attributes: {},
            setAttribute(name, value) { this.attributes[name] = value; },
            addEventListener(name, callback) { listeners[name] = callback; },
            fire(name, event) { listeners[name](event); }, focus() { this.focused = true; },
        };
    };
    const items = Array.from(html.matchAll(/data-question-id="(\d+)"\s+data-question-mode="(\w+)"\s+data-question-type="(\w+)"([^>]*)>([^<]*)<\/button>/g),
        ([, questionId, questionMode, questionType, attributes, title]) => ({ ...mockElement({ questionId, questionMode, questionType }, attributes), title }));
    const ids = items.map(item => item.dataset.questionId);
    const practiceWords = items.filter(item => item.dataset.questionMode === 'practice' && item.dataset.questionType === 'word');
    assert.deepEqual(practiceWords.map(item => item.dataset.questionId), ['11', '21', '22', '32', '33', '34', '35']);
    assert.equal(practiceWords[0].title, 'れんしゅうのたんご');
    assert.deepEqual(items.filter(item => item.disabled).map(item => item.dataset.questionId), ['32', '33', '34', '35']);
    assert.ok(!ids.includes('31'));

    const tabIds = Array.from(html.matchAll(/data-tab-id="(\w+)"/g), ([, id]) => id);
    assert.deepEqual(tabIds, ['practice', 'game']);
    const tabs = tabIds.map(tabId => ({ ...mockElement({ tabId }), id: `typing-tab-${tabId}` }));
    const start = mockElement({ gameUrl: 'http://localhost/typing/game' });
    const panel = mockElement({});
    const screen = {
        dataset: { activeTab: 'practice', initialQuestion: html.match(/data-initial-question="(\d+)"/)[1] },
        querySelectorAll: selector => selector === '[data-tab-id]' ? tabs : items,
        querySelector: selector => selector === '[data-typing-start]' ? start : panel,
    };
    let destination;
    vm.runInNewContext(readScripts('typing-select.js')[0], {
        URL, window: { location: { origin: url.origin, assign: target => { destination = target; } } },
        document: { querySelector: () => screen },
    });
    assert.equal(practiceWords[0].attributes['aria-pressed'], 'true');
    assert.equal(start.disabled, false);
    tabs[0].fire('keydown', { key: 'ArrowRight', preventDefault() {} });
    assert.equal(tabs[1].focused, true);
    assert.deepEqual(items.filter(item => !item.hidden).map(item => item.dataset.questionId), ['13', '14']);
    assert.equal(start.disabled, true);
    items.find(item => item.dataset.questionId === '14').fire('click');
    start.fire('click');
    assert.deepEqual(Object.fromEntries(destination.searchParams), { mode: 'game', type: 'long', set: '14' });
    tabs[1].fire('keydown', { key: 'ArrowRight', preventDefault() {} });
    assert.equal(tabs[0].attributes['aria-selected'], 'true');
    assert.equal(practiceWords[0].attributes['aria-pressed'], 'true');
    tabs[1].fire('click');
    assert.equal(items.find(item => item.dataset.questionId === '14').attributes['aria-pressed'], 'true');
    assert.match(renderRoute(url, 'empty'), /data-typing-start[^>]*disabled/);
});

test('game rejects invalid, private, empty, unreadable and mismatched sets', () => {
    for (const query of ['mode=practice&type=word&set=999', 'mode=practice&type=word&set=31',
        'mode=practice&type=word&set=32', 'mode=practice&type=word&set=33', 'mode=practice&type=word&set=34',
        'mode=practice&type=word&set=35',
        'mode=game&type=word&set=11', 'mode=practice&type=long&set=11',
        'mode=program&type=word&set=11', 'mode=practice&type=short&set=11',
        'mode=practice&type=word&set=word-1', 'mode=practice&type=word']) {
        const url = new URL(`http://localhost/typing/game?${query}`);
        assert.equal(requestRoute(url).status, 404, url.href);
    }
});

test('selection, game and result redirect guests to the existing login route', () => {
    for (const path of ['/typing', '/typing/game?mode=practice&type=word&set=11', '/typing/result?mode=practice&type=word&set=11']) {
        const response = requestRoute(new URL(path, 'http://localhost'), 'guest');
        assert.equal(response.status, 302);
        assert.equal(new URL(response.location).pathname, '/login');
    }
});
