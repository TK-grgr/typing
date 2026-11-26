// ゲーム画面の入力、出題、時間計測、進行の復元、成績保存を制御する。
(() => {
    'use strict';
    const root = document.querySelector('.typing-game[data-question-set]');
    if (!root || !window.TypingProgress) return;
    const practice = root.dataset.mode === 'practice';
    const comparison = window.TypingPractice;
    const text = root.querySelector('[data-typing-text]');
    const kana = root.querySelector('[data-typing-kana]');
    const guide = root.querySelector('[data-typing-romaji]');
    const keyboardInput = root.querySelector('[data-typing-keyboard-input]');
    const previousButton = root.querySelector('[data-typing-previous]');
    const nextButton = root.querySelector('[data-typing-next]');
    const startPrompt = root.querySelector('[data-typing-start]');
    const clock = root.querySelector('[data-typing-elapsed]');
    const saveStatus = root.querySelector('[data-typing-save-status]');
    const retry = root.querySelector('[data-typing-save-retry]');
    const exit = root.querySelector('[data-typing-exit]');
    if (!text || !startPrompt || !clock ||
        (practice ? !comparison || !keyboardInput || !previousButton || !nextButton : !window.TypingRomaji || !kana || !guide)) return;

    const questions = JSON.parse(root.dataset.questions);
    if (!questions.length) return;
    // ゲームは読み仮名ごとに判定器を準備し、実践は本文を照合用に渡す。
    const compiledQuestions = practice ? questions : questions.map(question => window.TypingRomaji.compile(question.kana));
    const minimumKeystrokes = practice ? 0 : compiledQuestions.reduce((total, question) => total + question.minimumLength, 0);
    const progress = window.TypingProgress;
    const identity = { userId: root.dataset.playerId, setId: root.dataset.questionSet,
        mode: root.dataset.mode, type: root.dataset.questionType, revision: root.dataset.questionRevision };
    // F5後も開始時刻と比較できるよう、ページの基準時刻と経過時間を足してミリ秒で扱う。
    const now = () => performance.timeOrigin + performance.now();
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrfToken };

    const keys = [...root.querySelectorAll('.typing-game__key[data-key]')];
    let ready = false;
    let abandoned = false;
    let attempt = root.dataset.attempt;
    let order;
    let completedInputs = [];
    let questionIndex = 0;
    let state;
    let finished = false;
    let completedPlay;
    let saving = false;
    let saved = false;
    let saveState = 'idle';
    let savedReceipt = null;
    let startedAt = null;
    let clockInterval;
    let correct = 0, miss = 0, timer;
    let answers = practice ? questions.map(() => '') : null;
    let checked = practice ? questions.map(() => false) : null;
    let reviewedCounts = practice ? questions.map(() => null) : null;

    // ゲームの打鍵履歴または実践の回答に、時刻・保存状態を添えて記録する。
    function saveProgress() {
        if (abandoned) return;
        progress.write({ identity, attempt, order, questionIndex, completedInputs,
            typed: finished ? '' : state?.typed ?? '', correct, miss, startedAt,
            elapsedMs: completedPlay?.elapsedMs ?? null, saveState, receipt: savedReceipt,
            ...(practice ? { practice: { answers, checked } } : {}) });
    }
    const normalizeKeyName = name => name?.replace(/\s+/g, '').toUpperCase();
    const keysByName = new Map(keys.map(key => [normalizeKeyName(key.dataset.key), key]));
    // 物理キーのコードとガイド用の文字を、JIS配列の仮想キーに対応づける。
    const jisCodes = {
        Backquote: '半角／全角', Lang5: '半角／全角', Minus: '-', Equal: '^', IntlYen: '¥',
        BracketLeft: '@', BracketRight: '[', Semicolon: ';', Quote: ':', Backslash: ']',
        Comma: ',', Period: '.', Slash: '/', IntlRo: 'ろ', NumpadEnter: 'Enter',
        ShiftLeft: 'Left Shift', ShiftRight: 'Right Shift', CapsLock: 'Caps Lock',
        ControlLeft: 'Left Ctrl', ControlRight: 'Right Ctrl', AltLeft: 'Left Alt', AltRight: 'Right Alt',
    };
    const jisLabels = {
        ' ': 'Space', Hankaku: '半角／全角', Zenkaku: '半角／全角', ZenkakuHankaku: '半角／全角',
        '!': '1', '"': '2', '#': '3', '$': '4', '%': '5', '&': '6', "'": '7',
        '(': '8', ')': '9', '=': '-', '~': '^', '|': '¥', '`': '@', '{': '[', '}': ']',
        '+': ';', '*': ':', '<': ',', '>': '.', '?': '/', '_': 'ろ', '\\': 'ろ',
    };
    const findKey = name => keysByName.get(normalizeKeyName(name));
    const guideKey = key => findKey(jisLabels[key] ?? key);
    // Shiftの左右やCaps Lockを区別し、コードが取れなければキー名と位置から判定する。
    const modifierCode = ({ code, key, location }) => {
        if (code === 'ShiftLeft' || code === 'ShiftRight' || code === 'CapsLock') return code;
        if (code && code !== 'Unidentified') return;
        if (key === 'CapsLock' || key === 'Alphanumeric') return 'CapsLock';
        if (key === 'Shift' && location === 1) return 'ShiftLeft';
        if (key === 'Shift' && location === 2) return 'ShiftRight';
    };
    const physicalKey = event => {
        const { code, key } = event;
        const modifier = modifierCode(event);
        if (modifier) return findKey(jisCodes[modifier]);
        const name = jisCodes[code] ?? code?.replace(/^(Key(?=[A-Z]$)|Digit(?=\d$))/, '');
        return findKey(name) ?? guideKey(key);
    };
    // 物理キーの押下表示は正打・ミスの判定から独立させ、フォーカスを失ったら解除する。
    const pressed = new Map();
    const pressId = event => modifierCode(event) ?? (
        event.code && event.code !== 'Unidentified' ? event.code : `${event.location}:${event.key.toUpperCase()}`
    );
    document.addEventListener('keydown', event => {
        if (practice) return;
        const key = physicalKey(event);
        if (key && !event.repeat) {
            pressed.set(pressId(event), key);
            key.classList.add('is-pressed');
        }
    }, true);
    document.addEventListener('keyup', event => {
        if (practice) return;
        const id = pressId(event);
        const key = pressed.get(id);
        pressed.delete(id);
        if (key && ![...pressed.values()].includes(key)) key.classList.remove('is-pressed');
    }, true);
    window.addEventListener('blur', () => {
        pressed.forEach(key => key.classList.remove('is-pressed'));
        pressed.clear();
    });
    // 入力済み部分、残りのガイド、次に受け付けるキーの黄色表示を更新する。
    function render() {
        const done = document.createElement('span');
        done.style.color = '#777';
        done.textContent = state.typed.toUpperCase();
        const remaining = state.complete ? '' : state.remaining;
        guide.replaceChildren(done, document.createTextNode(remaining.toUpperCase()));
        keys.forEach(k => k.classList.remove('is-next'));
        if (!state.complete) {
            for (const key of state.nextKeys) {
                // ガイドにない記号の別入力は、キーに印字された文字との混同を避けるため黄色表示しない。
                if (key === remaining[0] || key === ' ' || !Object.hasOwn(jisLabels, key)) {
                    guideKey(key)?.classList.add('is-next');
                }
            }
        }
        root.dataset.inputComplete = String(state.complete);
        root.dataset.correctCount = String(correct);
        root.dataset.missCount = String(miss);
    }
    function clearFeedback() {
        clearTimeout(timer);
        keys.forEach(k => k.classList.remove('is-correct','is-wrong'));
    }
    // 押したキーを正打なら青、ミスなら赤にし、短時間で元の色に戻す。
    function feedback(event, ok) {
        clearFeedback();
        physicalKey(event)?.classList.add(ok ? 'is-correct' : 'is-wrong');
        timer = setTimeout(clearFeedback, 180);
    }
    // 離れた問題は確定時の件数、表示中の問題は編集中の件数を使い、重複を防ぐ。
    function practiceCounts() {
        const current = finished ? -1 : order[questionIndex];
        const total = current < 0 ? comparison.empty() : comparison.compare(questions[current].text, answers[current]);
        reviewedCounts.forEach((counts, index) => {
            if (counts && index !== current) {
                for (const key of Object.keys(total)) total[key] += counts[key];
            }
        });
        return total;
    }
    function showPracticeCounts() {
        const counts = practiceCounts();
        for (const key of ['typo', 'omission', 'extra']) {
            root.querySelector(`[data-typing-${key}]`).textContent = String(counts[key]);
        }
        return counts;
    }
    // 出題順とは別に保持した回答を呼び戻し、入力欄と前後の操作ボタンを更新する。
    function showPracticeQuestion(index) {
        keyboardInput.value = answers[index];
        keyboardInput.hidden = false;
        keyboardInput.readOnly = finished;
        previousButton.disabled = finished || questionIndex === 0;
        nextButton.disabled = finished;
        nextButton.textContent = questionIndex >= questions.length - 1 ? '終了する' : '次の問題へ';
        showPracticeCounts();
        if (!finished) {
            keyboardInput.focus({ preventScroll: true });
            keyboardInput.setSelectionRange(keyboardInput.value.length, keyboardInput.value.length);
        }
    }

    // 実践では標準のIMEと編集操作を使い、確定済みの回答だけを集計・保存する。
    if (practice) {
        let composing = false;
        let pendingMove = 0;
        let flushTimer;
        const recordAnswer = () => {
            if (composing || abandoned || finished || startedAt === null) return;
            answers[order[questionIndex]] = keyboardInput.value;
            showPracticeCounts();
            saveProgress();
        };
        const flushMove = () => {
            clearTimeout(flushTimer);
            // compositionendの後に届く最終inputも待ってから、問題を切り替える。
            flushTimer = setTimeout(() => {
                if (composing || abandoned || finished) return;
                if (!pendingMove) {
                    recordAnswer();
                    return;
                }
                const index = order[questionIndex];
                answers[index] = keyboardInput.value;
                // 問題を離れる時点で、未入力の末尾も脱字に含めて集計する。
                checked[index] = true;
                reviewedCounts[index] = comparison.compare(questions[index].text, answers[index], true);
                questionIndex += pendingMove;
                pendingMove = 0;
                if (questionIndex === questions.length) finishGame();
                else {
                    showQuestion();
                    saveProgress();
                }
            }, 0);
        };
        const move = direction => {
            if (startedAt === null || finished || abandoned || pendingMove || questionIndex + direction < 0) return;
            pendingMove = direction;
            if (composing) keyboardInput.blur();
            flushMove();
        };
        keyboardInput.addEventListener('compositionstart', () => { composing = true; });
        keyboardInput.addEventListener('compositionend', () => {
            composing = false;
            flushMove();
        });
        keyboardInput.addEventListener('input', event => { if (!event.isComposing) recordAnswer(); });
        for (const name of ['copy', 'paste', 'drop']) {
            keyboardInput.addEventListener(name, event => event.preventDefault());
        }
        keyboardInput.addEventListener('beforeinput', event => {
            if (['insertFromPaste', 'insertFromDrop', 'insertFromYank'].includes(event.inputType)) event.preventDefault();
        });
        previousButton.addEventListener('click', () => move(-1));
        nextButton.addEventListener('click', () => move(1));
    }

    // 出題順に沿って表示と判定器を切り替える。F5からの復元時は再現済みの状態を使う。
    function showQuestion(restoredState) {
        clearFeedback();
        const index = order[finished ? questionIndex - 1 : questionIndex];
        const question = questions[index];
        text.textContent = question.text;
        if (practice) {
            showPracticeQuestion(index);
            return;
        }
        kana.textContent = question.kana;
        state = restoredState ?? compiledQuestions[index].create();
        render();
    }
    function showElapsed(elapsedMs) {
        const seconds = Math.floor(elapsedMs / 1000);
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        clock.textContent = `${minutes}:${String(seconds % 60).padStart(2, '0')}`;
        root.dataset.elapsedMs = String(elapsedMs);
    }
    // 更新処理が間引かれても、開始時刻との差で実際の経過時間を求める。
    function updateClock() {
        const elapsedMs = Math.max(0, Math.floor(now() - startedAt));
        showElapsed(elapsedMs);
        return elapsedMs;
    }
    function showPlay(restoredState) {
        startPrompt.hidden = true;
        text.hidden = false;
        if (!practice) kana.hidden = guide.hidden = false;
        showQuestion(restoredState);
        if (finished) {
            showElapsed(completedPlay.elapsedMs);
        } else {
            updateClock();
            clockInterval = setInterval(updateClock, 250);
        }
    }
    // SPACEで新規プレイを開始し、この時点から計時する。前回の結果表示用データはここで破棄する。
    function startGame() {
        startedAt = now();
        try {
            window.sessionStorage.removeItem('typing.playResult');
        } catch {
            // ストレージが無効でもプレイを開始できる。
        }
        showPlay();
        saveProgress();
    }
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && startedAt !== null && !finished && !abandoned) updateClock();
    });
    function playSummary(elapsedMs) {
        if (practice) {
            const counts = practiceCounts();
            const miss = counts.typo + counts.omission + counts.extra;
            return { mode: identity.mode, type: identity.type, setId: identity.setId, correct: counts.correct, miss, elapsedMs, counts };
        }
        return { mode: identity.mode, type: identity.type, setId: identity.setId,
            correct, miss, elapsedMs, minimumKeystrokes };
    }
    // ゲームの全問正解または実践の終了操作で、成績と経過時間を確定する。
    function finishGame() {
        finished = true;
        clearInterval(clockInterval);
        const elapsedMs = updateClock();
        clearFeedback();
        if (practice) {
            keyboardInput.readOnly = previousButton.disabled = nextButton.disabled = true;
            showPracticeCounts();
        } else render();
        completedPlay = playSummary(elapsedMs);
        saveResult();
    }

    // 送信中・保存済みの再送を防ぎ、再試行やF5後も同じプレイトークンを使う。
    // サーバー側はこのトークンで二重保存を防ぐ。
    async function saveResult() {
        if (!completedPlay || saving || saved || abandoned) return;
        saving = true;
        saveState = 'pending';
        saveProgress();
        if (retry) retry.hidden = true;
        if (saveStatus) {
            saveStatus.hidden = false;
            saveStatus.textContent = '成績を保存しています。';
        }

        let receipt;
        try {
            const { correct, miss, elapsedMs } = completedPlay;
            const response = await fetch(root.dataset.saveUrl, {
                method: 'POST',
                headers,
                body: JSON.stringify(practice ? { attempt, elapsedMs, answers: JSON.stringify(answers) } : { attempt, correct, miss, elapsedMs }),
            });
            if (!response.ok) throw new Error('Result save failed');
            receipt = await response.json();
            if (!progress.validReceipt(receipt, practice)) {
                throw new Error('Invalid save response');
            }
        } catch {
            showSaveFailure();
            return;
        } finally {
            saving = false;
        }

        if (abandoned) return;
        saved = true;
        savedReceipt = receipt;
        saveState = 'saved';
        saveProgress();
        openResult();
    }

    function showSaveFailure() {
        if (abandoned) return;
        saveState = 'failed';
        saveProgress();
        if (saveStatus) {
            saveStatus.hidden = false;
            saveStatus.textContent = '成績を保存できませんでした。通信やログイン状態を確認して再送してください。';
        }
        if (retry) retry.hidden = false;
    }

    // 確定した成績と順位を所有ユーザーIDと一緒に保存し、結果画面へ遷移する。
    function openResult() {
        try {
            const receiptCounts = savedReceipt?.counts;
            const result = { ...completedPlay, ...savedReceipt, userId: identity.userId };
            if (practice) {
                result.correct = receiptCounts.correct;
                result.miss = receiptCounts.typo + receiptCounts.omission + receiptCounts.extra;
            }
            window.sessionStorage.setItem('typing.playResult', JSON.stringify(result));
        } catch {
            // 保存できない場合も結果画面の案内と再プレイは利用できる。
        }
        window.location.assign(new URL(root.dataset.resultUrl, window.location.href));
    }
    retry?.addEventListener('click', saveResult);
    // 通常の途中終了では進行記録を破棄する。修飾キーで別タブを開く場合は現在のプレイを維持する。
    exit?.addEventListener('click', event => {
        if (event.defaultPrevented || event.button > 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        abandoned = true;
        clearInterval(clockInterval);
        progress.clear();
    });
    // 開始前のSPACEは開始専用。開始後はゲームのみ打鍵判定し、実践の入力はIMEに任せる。
    document.addEventListener('keydown', event => {
        if (!ready || abandoned || finished || event.isComposing || event.keyCode===229 || event.repeat || event.ctrlKey || event.altKey || event.metaKey || event.key.length!==1 || event.target?.closest?.('input,textarea,select,[contenteditable=true]')) return;
        const key=event.key.toLowerCase();
        if (practice && startedAt !== null) return;
        if (!/^[\x20-\x7e]$/.test(key)) return;
        if (event.defaultPrevented || (key === ' ' && event.target?.closest?.('button,[role="button"],summary'))) return;
        if (key === ' ') event.preventDefault();
        if (startedAt === null) {
            if (key === ' ') startGame();
            return;
        }
        const ok=state.push(key);
        if(ok) correct++; else miss++;
        if (state.complete) {
            completedInputs.push(state.typed);
            questionIndex++;
            if (questionIndex < questions.length) {
                showQuestion();
                saveProgress();
            } else finishGame();
            return;
        }
        render();
        feedback(event, ok);
        saveProgress();
    });
    if (!practice) root.dataset.minimumKeystrokes = String(minimumKeystrokes);
    root.dataset.inputComplete = 'false';
    root.dataset.correctCount = '0';
    root.dataset.missCount = '0';
    root.dataset.elapsedMs = '0';

    // 通常遷移や再プレイでは、問題の添字をフィッシャー・イェーツ法で並べ替えて出題順を決める。
    function startFresh() {
        order = questions.map((_, index) => index);
        for (let i = order.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [order[i], order[j]] = [order[j], order[i]];
        }
        try {
            window.history.replaceState({ ...window.history.state, typingAttempt: attempt }, '');
        } catch {
            // 履歴を保存できない環境でもゲーム自体は開始できる。
        }
        ready = true;
        saveProgress();
    }

    // F5で復元候補が見つかったら、サーバーでトークンと問題データを照合してから再開する。
    async function initialize() {
        const previous = progress.read(identity, compiledQuestions, now());
        if (!previous) {
            startFresh();
            return;
        }

        try {
            const response = await fetch(root.dataset.resumeUrl, {
                method: 'POST', headers, body: JSON.stringify({ attempt: previous.attempt, currentAttempt: attempt }),
            });
            if (abandoned) return;
            if (response.status === 422) {
                startFresh();
                return;
            }
            if (!response.ok) throw new Error('Could not validate saved play');
        } catch {
            if (!abandoned && saveStatus) {
                startPrompt.hidden = true;
                saveStatus.hidden = false;
                saveStatus.textContent = 'プレイを確認できませんでした。ページを再読み込みしてください。';
            }
            return;
        }

        ({ attempt, order, questionIndex, completedInputs, correct, miss, startedAt, saveState } = previous);
        if (practice) {
            ({ answers, checked } = previous.practice);
            // 復元した回答から確定済みの問題の内訳を再計算する。
            reviewedCounts = checked.map((reviewed, index) =>
                reviewed ? comparison.compare(questions[index].text, answers[index], true) : null);
        }
        if (startedAt !== null) {
            finished = questionIndex === questions.length;
            if (finished) completedPlay = playSummary(previous.elapsedMs);
            showPlay(previous.state);
        }
        ready = true;
        // 保存済みなら受け取った順位を使い、応答待ちなら同じ成績を送る。失敗時は再送ボタンを表示する。
        if (saveState === 'saved') {
            saved = true;
            savedReceipt = previous.receipt;
            openResult();
        } else if (saveState === 'pending') {
            saveResult();
        } else if (saveState === 'failed') {
            showSaveFailure();
        }
    }
    initialize();
})();
