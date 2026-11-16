const words = window.typingData;
const targetText = document.getElementById('targetText');
const typingInput = document.getElementById('typingInput');
const questionCount = document.getElementById('questionCount');
let scrore = 0;
let time = 0;
let count = 1;
let inWord = "";

function newWord(){
    if(words.length === 0){
    alert("終了");
    return;
}
    typingInput.value = "";
    inWord = words.splice(Math.floor(Math.random()* words.length),1)[0];
    targetText.textContent = inWord;
}

typingInput.addEventListener("keydown",(event) => {
    if(event.key === "Enter" && typingInput.value === inWord){
        newWord();
        count++;
        questionCount.textContent = count + " / 10";
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

window.onload = () => {
        newWord();
        typingInput.focus();
      };