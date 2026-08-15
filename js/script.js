alert("Welcome to Student Hub!");


// =========================
// DARK MODE
// =========================

function toggleTheme() {
    document.body.classList.toggle("dark");
}


// =========================
// REGISTER
// =========================

function validateRegister() {

    let name = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let mobile = document.getElementById("mobile").value;
    let password = document.getElementById("password").value;
    let confirm = document.getElementById("confirm").value;
    let course = document.getElementById("course").value;
    let year = document.getElementById("year").value;


    // Name
    if (name == "") {
        alert("Please enter your name");
        return false;
    }


    // Email
    if (email == "") {
        alert("Please enter your email");
        return false;
    }

    if (!email.includes("@")) {
        alert("Enter a valid email");
        return false;
    }


    // Mobile
    if (!/^[0-9]{10}$/.test(mobile)) {
        alert("Enter a valid 10 digit mobile number");
        return false;
    }


    // Password
    if (password == "") {
        alert("Please enter password");
        return false;
    }

    if (password.length < 8) {
        alert("Password must be at least 8 characters");
        return false;
    }


    // Confirm password
    if (confirm == "") {
        alert("Please confirm your password");
        return false;
    }

    if (password != confirm) {
        alert("Passwords do not match");
        return false;
    }


    // Course
    if (course == "") {
        alert("Please select course");
        return false;
    }


    // Year
    if (year == "") {
        alert("Please select year");
        return false;
    }


    // Gender
    let gender = document.querySelector(
        'input[name="gender"]:checked'
    );

    if (!gender) {
        alert("Please select gender");
        return false;
    }


    // Terms
    let terms = document.getElementById("terms");

    if (!terms.checked) {
        alert("Please accept terms");
        return false;
    }


    // Save email and password
    sessionStorage.setItem("email", email);
    sessionStorage.setItem("password", password);


    alert("Registration Successful!");

    // Go to login
    window.location.href = "login.html";

    return false;
}


// =========================
// PASSWORD STRENGTH
// =========================

let passwordBox = document.getElementById("password");

if (passwordBox) {

    passwordBox.onkeyup = function () {

        let strength = document.getElementById("strength");

        if (passwordBox.value.length < 6) {

            strength.innerHTML = "Weak";

        } else if (passwordBox.value.length < 8) {

            strength.innerHTML = "Medium";

        } else {

            strength.innerHTML = "Strong";
        }
    };
}


// =========================
// LOGIN
// =========================

function validateLogin() {

    let email = document.getElementById("loginEmail").value;
    let password = document.getElementById("loginPassword").value;


    // Get registered information
    let registeredEmail = sessionStorage.getItem("email");
    let registeredPassword = sessionStorage.getItem("password");


    // Check login
    if (
        email == registeredEmail &&
        password == registeredPassword
    ) {

        alert("Login Successful!");

        window.location.href = "dashboard.html";

    } else {

        alert("Invalid email or password");

    }

    return false;
}


// =========================
// DATE AND TIME
// =========================

function updateDateTime() {

    let dateElement = document.getElementById("dateTime");

    if (dateElement) {

        dateElement.innerHTML =
            new Date().toLocaleString();
    }
}

setInterval(updateDateTime, 1000);

updateDateTime();


// =========================
// BACK TO TOP
// =========================

window.onscroll = function () {

    let button = document.getElementById("topBtn");

    if (!button) {
        return;
    }

    if (document.documentElement.scrollTop > 100) {

        button.style.display = "block";

    } else {

        button.style.display = "none";
    }
};


function topFunction() {

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}


// =========================
// IMAGE SLIDER
// =========================

const images = [
    "images/campus1.webp",
    "images/events1.jpg",
    "images/library.jpg"
];

let currentImage = 0;


function changeImage() {

    let slider = document.getElementById("slider");

    if (!slider) {
        return;
    }

    currentImage++;

    if (currentImage >= images.length) {
        currentImage = 0;
    }

    slider.src = images[currentImage];
}

setInterval(changeImage, 3000);


// =========================
// SEARCH
// =========================

function searchPages() {

    let searchInput = document.getElementById("searchInput");

    if (!searchInput) {
        return;
    }

    let searchText = searchInput.value.toLowerCase().trim();


    if (searchText == "home") {
        window.location.href = "studenthub.html";

    } else if (searchText == "about") {
        window.location.href = "about.html";

    } else if (searchText == "discussion") {
        window.location.href = "discussion.html";

    } else if (searchText == "gallery") {
        window.location.href = "gallery.html";

    } else if (searchText == "register") {
        window.location.href = "register.html";

    } else if (searchText == "login") {
        window.location.href = "login.html";

    } else if (searchText == "dashboard") {
        window.location.href = "dashboard.html";

    } else if (searchText == "events") {
        window.location.href = "events.html";

    } else if (searchText == "profile") {
        window.location.href = "profile.html";

    } else if (searchText == "contact") {
        window.location.href = "contact.html";

    } else if (searchText == "feedback") {
        window.location.href = "feedback.html";

    } else if (searchText == "faq") {
        window.location.href = "faq.html";

    } else if (searchText == "news") {
        window.location.href = "news.html";

    } else if (searchText == "") {

        alert("Please enter something to search.");

    } else {

        alert("Page not found.");
    }
}


// Enter key for search

let searchInput = document.getElementById("searchInput");

if (searchInput) {

    searchInput.addEventListener(
        "keypress",
        function (event) {

            if (event.key == "Enter") {
                searchPages();
            }

        }
    );
}


// =========================
// SIMPLE LOGOUT
// =========================

function logoutUser() {

    sessionStorage.clear();

    alert("You have been logged out.");

    window.location.href = "login.html";
}


// =========================
// SIMPLE PROFILE
// =========================

function displayProfile() {

    let email = sessionStorage.getItem("email");

    let profileEmail =
        document.getElementById("profileEmail");

    if (profileEmail && email) {

        profileEmail.innerHTML = email;
    }
}