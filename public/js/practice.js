const words =[...window.typingData.details];
const targetText =document.getElementById('targetText');
const typingInput =document.getElementById('typingInput');
const questionCount =document.getElementById('questionCount');
const wordsLength = words.length;
let score = 0;
let time = 0;
let startTime;
let count = 0;
let correct = 0;
let inWord = "";
let CDtime = 3;
let started = false;

async function endGame()
{
    time =timerStop(startTime);
    score =calculateScore(correct,time);
    try{
        const result =
            await saveResult(
                window.typingData.questionId,
                score,
                correct,
                time,
                window.typingData.saveUrl
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
    typingInput.value = "";
    inWord = words.splice(Math.floor(Math.random()* words.length),1)[0];
    targetText.textContent = inWord;
}

typingInput.addEventListener("keydown",async (event) => {
    if(event.key === "Enter" && typingInput.value === inWord){
        correct += inWord.length;
        await newWord();
        count++;
        questionCount.textContent = count + " / " + wordsLength;
        
    }
});
// 旧式
// typingInput.addEventListener("input",() => {
//     if(typingInput.value === inWord){
//         newWord();
//         count++;
//         questionCount.textContent = count + " / 10";
//     }
// });

// window.onload = () => {
//         newWord();
//         typingInput.focus();
//       };

function countDown(){
    if(CDtime > 0){
        targetText.textContent = CDtime;
        CDtime--;
        setTimeout(countDown,1000);
    }else{
        targetText.textContent ="START!";
        setTimeout(() => {
            newWord();
            startTime =timerStart();typingInput.focus();},500);
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