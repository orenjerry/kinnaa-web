document.addEventListener("DOMContentLoaded", () => {
    const infoButton = document.getElementById("more-info");
    const loginForm = document.getElementById("login");
    const infoContent = document.getElementById("info-content");
    const container = document.getElementById("container");

    infoButton.addEventListener("click", () => {
        if (loginForm.style.display === "block") {
            loginForm.style.display = "none";
            infoButton.innerText = "Back to Login";
            infoContent.style.display = "block";
            container.classList.toggle("wider");
        } else {
            loginForm.style.display = "block";
            infoButton.innerText = "More Info";
            infoContent.style.display = "none";
            container.classList.toggle("wider");
        }
    });
});