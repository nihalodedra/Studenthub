
<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "secure_login");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
        $sql = "SELECT id, username, password, role
                FROM users
                WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["last_activity"] = time();

                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                if ($user["role"] === "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.html");
                }
                exit();
            } else {
                $error = "Invalid username or password.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Unable to process login. Please check your database table.";
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Hub - Login</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>Student Hub</h1>
    <p>Learn • Connect • Share • Grow</p>
</header>

<nav>
    <ul id="navLinks">
        <li><a href="studenthub.html">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="discussion.html">Discussion</a></li>
        <li><a href="gallery.html">Gallery</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="dashboard.html">Dashboard</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="profile.html">Profile</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li><a href="admin.php">Admin</a></li>
        <li><a href="feedback.html">Feedback</a></li>
        <li><a href="faq.html">FAQ</a></li>
        <li><a href="news.html">News</a></li>
    </ul>
</nav>

<main>
    <section>
        <h2>Login to Student Hub</h2>

        <?php if ($error !== ""): ?>
            <p style="color: red;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_GET["timeout"])): ?>
            <p style="color: red;">
                Your session expired. Please log in again.
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label><br>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username"
                required
            >

            <br><br>

            <label for="password">Password</label><br>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <br><br>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="studenthub.html">Back to Home</a></p>
    </section>
</main>

<footer>
    <p>© 2026 Student Hub | Developed by Nihal</p>

    <p>
        <a href="https://www.facebook.com/" target="_blank">Facebook</a> |
        <a href="https://www.instagram.com/" target="_blank">Instagram</a> |
        <a href="https://www.linkedin.com/" target="_blank">LinkedIn</a>
    </p>
</footer>

<script src="js/script.js"></script>

</body>
</html>
