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


function newWord(){
    if(words.length === 0){
    timerStop();
    score =Math.round((correct / Math.max(time,1))* 60);
    fetch(window.typingData.saveUrl,
        {
            method: 'POST',
            headers: {
                'Content-Type':
                'application/json',
                'X-CSRF-TOKEN':
            document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                question_id:
                window.typingData.questionId,
                score: score,
                correct: correct,
                input_time: time,
            })
        }
    )
    .then(response =>response.json())
    .then(data => {window.location.href ='/typing/public/result/'+data.result_id;});
    return;
}
    typingInput.value = "";
    inWord = words.splice(Math.floor(Math.random()* words.length),1)[0];
    targetText.textContent = inWord;
}

typingInput.addEventListener("keydown",(event) => {
    if(event.key === "Enter" && typingInput.value === inWord){
        correct += inWord.length;
        newWord();
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
        targetText.textContent = "START!";
        setTimeout(() => {newWord();timerStart();typingInput.focus();},500);
    }
}

function timerStart(){
  startTime = Date.now();
}

function timerStop(){
    time = Math.floor((Date.now() - startTime) / 1000);
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