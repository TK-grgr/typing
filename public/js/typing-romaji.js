// 読み仮名からローマ字入力の判定器を作り、入力候補と最短打鍵数を提供する。
(() => {
    'use strict';

    // 「し」の si・shi など、同じ読みで認める複数の打ち方を登録する。
    const map = {
        あ:['a'],い:['i'],う:['u'],え:['e'],お:['o'],か:['ka'],き:['ki'],く:['ku'],け:['ke'],こ:['ko'],
        さ:['sa'],し:['si','shi'],す:['su'],せ:['se'],そ:['so'],た:['ta'],ち:['ti','chi'],つ:['tu','tsu'],て:['te'],と:['to'],
        な:['na'],に:['ni'],ぬ:['nu'],ね:['ne'],の:['no'],は:['ha'],ひ:['hi'],ふ:['hu','fu'],へ:['he'],ほ:['ho'],
        ま:['ma'],み:['mi'],む:['mu'],め:['me'],も:['mo'],や:['ya'],ゆ:['yu'],よ:['yo'],ら:['ra'],り:['ri'],る:['ru'],れ:['re'],ろ:['ro'],わ:['wa'],を:['wo'],
        が:['ga'],ぎ:['gi'],ぐ:['gu'],げ:['ge'],ご:['go'],ざ:['za'],じ:['zi','ji'],ず:['zu'],ぜ:['ze'],ぞ:['zo'],だ:['da'],ぢ:['di'],づ:['du'],で:['de'],ど:['do'],
        ば:['ba'],び:['bi'],ぶ:['bu'],べ:['be'],ぼ:['bo'],ぱ:['pa'],ぴ:['pi'],ぷ:['pu'],ぺ:['pe'],ぽ:['po'],
        きゃ:['kya'],きゅ:['kyu'],きょ:['kyo'],しゃ:['sya','sha'],しゅ:['syu','shu'],しょ:['syo','sho'],ちゃ:['tya','cha'],ちゅ:['tyu','chu'],ちょ:['tyo','cho'],
        にゃ:['nya'],にゅ:['nyu'],にょ:['nyo'],ひゃ:['hya'],ひゅ:['hyu'],ひょ:['hyo'],みゃ:['mya'],みゅ:['myu'],みょ:['myo'],りゃ:['rya'],りゅ:['ryu'],りょ:['ryo'],
        ぎゃ:['gya'],ぎゅ:['gyu'],ぎょ:['gyo'],じゃ:['zya','ja','jya'],じゅ:['zyu','ju','jyu'],じょ:['zyo','jo','jyo'],びゃ:['bya'],びゅ:['byu'],びょ:['byo'],ぴゃ:['pya'],ぴゅ:['pyu'],ぴょ:['pyo'],
        てぃ:['thi','texi'],でぃ:['dhi','dexi'],ふぁ:['fa','fuxa'],
        'ー':['-'],'、':[','],'。':['.'],'・':['/'],'「':['['],'」':[']'],'　':[' '],' ':[' '],'！':['!'],'？':['?'],'（':['('],'）':[')'],'：':[':'],'；':[';'],'／':['/'],'－':['-']
    };
    // 全文の候補は列挙せず、読みの位置ごとの候補だけを持つ。
    // 読みの位置ごとに遷移を作り、後ろから最短の入力経路を求める。
    function compile(text) {
        const chars = Array.from(text.normalize('NFKC').toLowerCase(), c => /[ァ-ヶ]/.test(c) ? String.fromCharCode(c.charCodeAt(0)-96) : c);
        const n = chars.length, edges = Array.from({length:n+1},()=>[]), best = Array(n+1).fill('');
        for(let i=n-1;i>=0;i--) {
            const c=chars[i], pair=c+(chars[i+1]||'');
            const add=(word,to)=>edges[i].push({word,to});
            // 撥音の n 単独入力は後続の音で制限する。促音は xtu などの明示入力と子音の重ね打ちを扱う。
            if(c==='ん') {
                add('nn',i+1); add("n'",i+1);
                if(i+1===n || !/^[あいうえおやゆよん]/.test(chars[i+1])) add('n',i+1);
            } else if(c==='っ') {
                add('xtu',i+1); add('ltu',i+1); add('xtsu',i+1); add('ltsu',i+1);
                for(const e of edges[i+1]) {
                    if(chars[i+1]!=='っ' && /^[bcdfghjklmpqrstvwxyz]/.test(e.word)) add(e.word[0]+e.word,e.to);
                    if(e.word.startsWith('ch')) add('t'+e.word,e.to);
                }
            } else {
                const small={ゃ:['xya','lya'],ゅ:['xyu','lyu'],ょ:['xyo','lyo'],ぁ:['xa','la'],ぃ:['xi','li'],ぅ:['xu','lu'],ぇ:['xe','le'],ぉ:['xo','lo']};
                // 拗音などをまとめて入力する経路と、仮名を個別に打つ経路を残す。
                if(map[pair]) for(const v of map[pair]) add(v,i+2);
                for(const v of map[c]||small[c]||[c]) add(v,i+1);
            }
            best[i]=edges[i].map(e=>e.word+best[e.to]).reduce((a,b)=>a.length<=b.length?a:b);
        }
        // 1問ごとに独立した状態を作り、同じ入力途中まで一致する候補をすべて保持する。
        function create() {
            let states=[{rest:'',to:0}], typed='';
            const options=()=>states.flatMap(s=>s.rest?[s]:edges[s.to].map(e=>({rest:e.word,to:e.to,from:s.to,length:e.word.length})));
            const complete=()=>states.some(s=>!s.rest&&s.to===n);
            // 有効な候補すべてが通過した読みの境界まで表示し、残りの打鍵はローマ字で残す。
            function displayInput() {
                if (complete()) return chars.join('');

                let confirmed = n, pending = 0;
                for (const { rest, to, from, length } of states) {
                    const entered = rest ? length - rest.length : 0;
                    // 子音の重ね打ちは、2打目で「っ」が確定し、2打目自体は次の音に使う。
                    const geminate = rest && entered >= 2 && chars[from] === 'っ' && to > from + 1 ? 1 : 0;
                    const boundary = rest ? from + geminate : to;
                    const suffix = entered - geminate;

                    if (boundary < confirmed) {
                        confirmed = boundary;
                        pending = suffix;
                    } else if (boundary === confirmed) {
                        pending = Math.max(pending, suffix);
                    }
                }

                return chars.slice(0, confirmed).join('') + (pending ? typed.slice(-pending) : '');
            }
            return {
                // 次の文字が一致する候補だけを進め、候補がなければ現在の状態を維持する。
                push(key) {
                    key=key.toLowerCase();
                    const next=options().filter(s=>s.rest[0]===key).map(s=>({...s,rest:s.rest.slice(1)}));
                    if(!next.length)return false;
                    states=[...new Map(next.map(s=>[s.to+':'+s.rest,s])).values()]; typed+=key; return true;
                },
                get complete(){return complete();},
                get typed(){return typed;},
                get displayInput(){return displayInput();},
                get nextKeys(){return [...new Set(options().map(s=>s.rest[0]))];},
                get remaining(){return states.map(s=>s.rest+best[s.to]).reduce((a,b)=>a.length<=b.length?a:b);}
            };
        }
        return {create,minimumLength:best[0].length};
    }
    const api={compile, minimumLength:k=>compile(k).minimumLength,
        accepts(k,p){const s=compile(k).create();return Array.from(p).every(c=>s.push(c));},
        matches(k,p){const s=compile(k).create();return Array.from(p).every(c=>s.push(c))&&s.complete;}};
    if(typeof window!=='undefined')window.TypingRomaji=api;
    if(typeof module!=='undefined'&&module.exports)module.exports=api;
})();
