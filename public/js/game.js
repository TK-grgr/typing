const words =[...window.typingData.details];
const targetText =document.getElementById('targetText');
const questionCount =document.getElementById('questionCount');
const ReadingText = document.getElementById('ReadingText');
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
let inWord = "";//入力予定文字
let typingText = null;



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
    typingText = new TypingText(inWord.reading);
    targetText.textContent = displayText;
    ReadingText.textContent = typingText.remainingRoman;

}
//文字入力正誤判定+現在問題カウント+現在入力文字位置取得
window.addEventListener("keydown",async (event) => {
    //シフト、コントロール、エンター、Spaceなどを無視。
    if (!TypingText.isValidInputKey(event.key)) {
    return;
}
    if(!started){
        return;
    }
    if(!typingText){
    return;
}
if(event.repeat){
        return;
    }

    const result = typingText.inputKey(event.key);
    switch(result){
        case "unmatch":
            miss++;
            missCount.textContent = "ミス:" + miss;
            break;
        case "incomplete":
            correct++;
            ReadingText.innerHTML = '<span class="done">' + typingText.completedRoman + '</span>' + typingText.remainingRoman;
            break;
        case 'complete':
            correct++;
            count++;
            questionCount.textContent = count + '/' + wordsLength;
            await newWord();
            break;
    }
    
});


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


function calculateScore(){
    const accuracy = correct / Math.max(correct + miss,1);
    return Math.round(((correct /Math.max(time,1)) * 60) * accuracy );
}