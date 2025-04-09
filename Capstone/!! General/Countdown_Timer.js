// Global variable to store the countdown interval
let timer = null;

document.addEventListener("DOMContentLoaded", function () {
    // Check for remaining time in localStorage
    const remainingTime = localStorage.getItem("remainingTime");

    if (remainingTime) {
        // Start the countdown with the remaining time
        startCountdown(parseInt(remainingTime));
    }
});

function startCountdown(totalMinutes) {
    let remainingTime = totalMinutes;

    // Clear the previous timer if it exists
    if (timer) {
        clearInterval(timer);
    }

    // Start a new timer
    timer = setInterval(() => {
        remainingTime--;

        // Update localStorage with the new remaining time
        localStorage.setItem("remainingTime", remainingTime);

        // Update the status display (only if the element exists)
        const statusElement = document.getElementById('screen-time-status');
        if (statusElement) {
            statusElement.textContent = `Status: On (${Math.floor(remainingTime / 60)}h ${remainingTime % 60}m remaining)`;
        }

        if (remainingTime <= 0) {
            clearInterval(timer);
            alert("Screen time limit reached!");
            localStorage.removeItem("remainingTime"); // Clear localStorage

            // Update the status display (only if the element exists)
            if (statusElement) {
                statusElement.textContent = "Status: Off";
            }
        }
    }, 60000); // Update every minute (60,000 milliseconds)
}