function timerStart() {
    return Date.now();
}

function timerStop(startTime) {
    return Math.floor(
        (Date.now() - startTime)/ 1000);
}
function calculateScore(correct,time) {
    return Math.round((correct / Math.max(time, 1)) * 60);
}
async function saveResult(
    questionId,
    score,
    correct,
    inputTime,
    saveUrl,
    miss = 0,
    
) {
    const response =
        await fetch(
            saveUrl,
            {method: 'POST',headers: {
                    'Content-Type':
                        'application/json',
                    'X-CSRF-TOKEN':
                        document
                        .querySelector('meta[name="csrf-token"]').content,},
                body: JSON.stringify({
                    question_id:questionId,
                    score:score,
                    correct:correct,
                    miss:miss,
                    input_time:inputTime,
                })
            }
        );
    return await response.json();
}