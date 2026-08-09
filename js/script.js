alert("Welcome to Student Hub!");

function toggleTheme() {
    document.body.classList.toggle("dark");
}
function validateLogin() {

    let email = document.getElementById("loginEmail").value.trim();
    let password = document.getElementById("loginPassword").value;

    // Get registered user
    let storedUser = localStorage.getItem("studentUser");

    if (storedUser === null) {
        alert("No registered user found. Please register first.");
        return false;
    }

    // Convert stored data into object
    let user = JSON.parse(storedUser);

    // Check email and password
    if (email === user.email && password === user.password) {

        // Save login status
        localStorage.setItem("isLoggedIn", "true");

        // Save the currently logged-in user
        localStorage.setItem("loggedInUser", JSON.stringify(user));

        alert("Login Successful!");

        window.location.href = "dashboard.html";

    } else {

        alert("Invalid email or password.");

    }

    return false;
}








function validateRegister() {

    let name = document.getElementById("name").value;
    let email = document.getElementById("registerEmail").value;
    let password = document.getElementById("registerPassword").value;

    if (name === "" || email === "" || password === "") {
        alert("Please fill in all fields.");
        return false;
    }

    if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }

    // Create student object
    let user = {
        name: name,
        email: email,
        password: password,
        role: "student"
    };

    // Save user in browser
    localStorage.setItem("studentUser", JSON.stringify(user));

    alert("Registration Successful!");

    // Open login page
    window.location.href = "login.html";

    return false;
}








function updateDateTime() {
    let dateElement = document.getElementById("dateTime");

    if (dateElement) {
        dateElement.innerHTML = new Date().toLocaleString();
    }
}

setInterval(updateDateTime, 1000);
updateDateTime();

window.onscroll = function () {

    let button = document.getElementById("topBtn");

    if (!button) return;

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

const images = [
    "images/campus1.webp",
    "images/events1.jpg",
    "images/library.jpg"
];

let currentImage = 0;












function changeImage() {

    const slider = document.getElementById("slider");

    if (!slider) return;

    currentImage++;

    if (currentImage >= images.length) {
        currentImage = 0;
    }

    slider.src = images[currentImage];
}

setInterval(changeImage, 3000);











function searchPages() {
    const searchText = document
        .getElementById("searchInput")
        .value
        .toLowerCase()
        .trim();

    const pages = {
        "home": "index.html",
        "about": "about.html",
        "discussion": "discussion.html",
        "gallery": "gallery.html",
        "register": "register.html",
        "login": "login.html",
        "dashboard": "dashboard.html",
        "events": "events.html",
        "profile": "profile.html",
        "contact": "contact.html",
        "admin": "admin.html",
        "feedback": "feedback.html",
        "faq": "faq.html",
        "news": "news.html"
    };

    if (pages[searchText]) {
        window.location.href = pages[searchText];
    } else if (searchText === "") {
        alert("Please enter something to search.");
    } else {
        alert("Page not found. Try: About, Events, Gallery, Profile, News, etc.");
    }
}
document.getElementById("searchInput").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        searchPages();
    }
});















// Display Profile Information

function displayProfile() {

    let loggedInUser = localStorage.getItem("loggedInUser");

    if (loggedInUser === null) {
        alert("Please login first.");
        window.location.href = "login.html";
        return;
    }

    let user = JSON.parse(loggedInUser);

    let profileName = document.getElementById("profileName");
    let profileEmail = document.getElementById("profileEmail");

    if (profileName) {
        profileName.textContent = user.name;
    }

    if (profileEmail) {
        profileEmail.textContent = user.email;
    }
}


// Logout User

function logoutUser() {

    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("loggedInUser");

    alert("You have been logged out.");

    window.location.href = "login.html";
}






// Display logged-in student's name on Dashboard

function displayUserName() {

    let loggedInUser = localStorage.getItem("loggedInUser");

    if (loggedInUser === null) {
        return;
    }

    let user = JSON.parse(loggedInUser);

    let welcomeMessage = document.getElementById("welcomeMessage");

    if (welcomeMessage) {
        welcomeMessage.textContent =
            "Welcome, " + user.name + "! 👋";
    }
}





// Protect Dashboard and Profile

function checkLogin() {

    let isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn !== "true") {

        alert("Please login first.");

        window.location.href = "login.html";

    }
}



// Update navigation based on login status

function updateNavigation() {

    let isLoggedIn = localStorage.getItem("isLoggedIn");

    let navLinks = document.getElementById("navLinks");

    if (!navLinks) {
        return;
    }

    if (isLoggedIn === "true") {

        let loggedInUser = localStorage.getItem("loggedInUser");

        if (loggedInUser === null) {
            return;
        }

        let user = JSON.parse(loggedInUser);

        let adminLink = "";

        // Show Admin only to admin
        if (user.role === "admin") {
            adminLink = `
                <li><a href="admin.html">Admin</a></li>
            `;
        }

        navLinks.innerHTML = `
            <li><a href="studenthub.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="discussion.html">Discussion</a></li>
            <li><a href="dashboard.html">Dashboard</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="profile.html">Profile</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="admin.html">Admin</a></li>
            <li><a href="feedback.html">Feedback</a></li>
            <li><a href="faq.html">FAQ</a></li>
            <li><a href="#" onclick="logoutUser()">Logout</a></li>
        `;

    } else {

        navLinks.innerHTML = `
            <li><a href="studenthub.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="discussion.html">Discussion</a></li>
            <li><a href="register.html">Register</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="admin.html">Admin</a></li>
            <li><a href="feedback.html">Feedback</a></li>
            <li><a href="faq.html">FAQ</a></li>
        `;
    }
}





// Protect Admin Page

function checkAdmin() {

    let isLoggedIn = localStorage.getItem("isLoggedIn");
    let loggedInUser = localStorage.getItem("loggedInUser");

    if (isLoggedIn !== "true" || loggedInUser === null) {

        alert("Please login first.");
        window.location.href = "login.html";
        return;
    }

    let user = JSON.parse(loggedInUser);

    if (user.role !== "admin") {

        alert("Access denied. Admin only.");
        window.location.href = "dashboard.html";
        return;
    }
}


// Display total registered users


function displayUserCount() {

    let storedUser = localStorage.getItem("studentUser");

    let totalUsers = document.getElementById("totalUsers");

    if (!totalUsers) {
        return;
    }

    if (storedUser === null) {
        totalUsers.textContent = "0";
    } else {
        totalUsers.textContent = "1";
    }
}








// Display Admin Name

function displayAdminName() {

    let loggedInUser = localStorage.getItem("loggedInUser");

    if (loggedInUser === null) {
        return;
    }

    let user = JSON.parse(loggedInUser);

    let adminWelcome = document.getElementById("adminWelcome");

    if (adminWelcome) {
        adminWelcome.textContent =
            "Welcome, " + user.name + "! ";
    }
}







// Manage Users

function manageUsers() {

    let section = document.getElementById("userManagement");

    let storedUser = localStorage.getItem("studentUser");

    if (storedUser === null) {
        alert("No registered user found.");
        return;
    }

    let user = JSON.parse(storedUser);

    document.getElementById("adminUserName").textContent = user.name;
    document.getElementById("adminUserEmail").textContent = user.email;
    document.getElementById("adminUserRole").textContent = user.role;

    section.style.display = "block";
}

function manageEvents() {
    alert("Event Management coming soon!");
}

function manageDiscussions() {
    alert("Discussion Management coming soon!");
}

function reviewFeedback() {
    alert("Feedback Review coming soon!");
}