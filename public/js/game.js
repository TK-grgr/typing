const words =[...window.typingData.details];
const targetText =document.getElementById('targetText');
const questionCount =document.getElementById('questionCount');
const RomanText = document.getElementById('romanText');
const missCount = document.getElementById('missCount');
const wordsLength = words.length;
let score = 0;//スコア
let time = 0;//経過時間
let startTime;//開始時刻の取得
let count = 0;//クリア済み問題のカウント
let correct = 0;//入力文字数
let CDtime = 3;//スタート後カウント時間
let started = false;//開始フラグ
let miss = 0;//ミス数
let currentIndex = 0;//入力中の文字位置取得
let displayText = "";//表示用文字
let currentText = "";//入力用文字
let inWord = "";//入力予定文字
let userInput = "";//過去の入力文字
// const romanMap = {//複数方式入力対応

//     si: ['si','shi'],

//     ti: ['ti','chi'],

//     tu: ['tu','tsu'],

//     hu: ['hu','fu'],

//     zi: ['zi','ji'],

//     sya: ['sya','sha'],

//     syu: ['syu','shu'],

//     syo: ['syo','sho'],

// };



async function endGame()
{
    time =timerStop(startTime);
    score =calculateScore();
    try{
        const result =
            await saveResult(
                window.typingData.questionId,
                score,
                correct,
                time,
                window.typingData.saveUrl,
                miss,
            );

        window.location.href ='/typing/public/result/' + result.result_id;
    }catch(error){
        console.error(error);
        alert('結果の保存に失敗しました');
    }
}


async function newWord(){
    if(words.length === 0){
    await endGame();
    return;
}
    inWord = words.splice(Math.floor(Math.random()* words.length),1)[0];
    displayText = inWord.text;
    currentText = inWord.roman;
    currentIndex = 0;
    targetText.textContent = displayText;
    updateRomanText();
}
//文字入力正誤判定+現在問題カウント+現在入力文字位置取得
window.addEventListener("keydown",async (event) => {
    //シフト、コントロール、エンター、Spaceなどを無視。
    if(event.key.length !== 1){
    return;
}
    if(!started){
        return;
    }
    if(!currentText){
    return;
}
    const currentChar = currentText[currentIndex];
    if(event.key === currentChar){
        currentIndex++;
        updateRomanText();
        correct++;
        if(currentIndex >= currentText.length){
            count ++;
            questionCount.textContent = count + " / " + wordsLength;
            await newWord();
        }
    }else{
        miss++;
        missCount.textContent ="ミス: " + miss;
    }
});

function updateRomanText(){
    const done = currentText.slice(0,currentIndex);
    const remain = currentText.slice(currentIndex);
    RomanText.innerHTML = '<span class="done">' + done + '</span>' + remain;
}

function countDown(){
    if(CDtime > 0){
        targetText.textContent = CDtime;
        CDtime--;
        setTimeout(countDown,1000);
    }else{
        targetText.textContent ="START!";
        setTimeout(() => {
            newWord();
            startTime =timerStart();},500);
    }
}

window.addEventListener('keydown', (e) => {
  if (e.code === "Space") {
    // 画面スクロールを防ぐ場合
    e.preventDefault();
    if(started){return;}
    e.preventDefault(); // フォームの自動送信などを防ぐ場合
    started = true;
    countDown();
  }
});

// function createCandidates(text){
//     let candidates = [text];
//     Object.entries(romanMap).forEach(([base, list]) => {
//         const next = [];
//         candidates.forEach(candidate => {
//             if(
//                 candidate.includes(base)
//             ){
//                 list.forEach(pattern => {
//                     next.push(candidate.replace(base,pattern));
//                 });
//             }else{next.push(candidate);}
//         });
//         candidates = next;});
//     return candidates;
// }


function calculateScore(){
    const accuracy = correct / Math.max(correct + miss,1);
    return Math.round(((correct /Math.max(time,1)) * 60) * accuracy );
}