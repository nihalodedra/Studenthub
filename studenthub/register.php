<?php
declare(strict_types=1);
session_start();

$conn = new mysqli("localhost", "root", "", "newcollege");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$csvFile = __DIR__ . "/registrations.csv";
$jsonFile = __DIR__ . "/registrations.json";

$errors = [];
$old = [
    "name" => "",
    "username" => "",
    "email" => "",
    "mobile" => "",
    "course" => "",
    "year" => "",
    "gender" => ""
];

$courses = ["AIML", "CSE", "IT"];
$years = ["1", "2", "3", "4"];
$genders = ["Male", "Female", "Other"];

if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    foreach ($old as $key => $_) {
        $old[$key] = trim((string)($_POST[$key] ?? ""));
    }

    $password = (string)($_POST["password"] ?? "");
    $confirm = (string)($_POST["confirm"] ?? "");

    if (!hash_equals($_SESSION["csrf"], (string)($_POST["csrf"] ?? ""))) {
        $errors[] = "Form expired. Refresh and try again.";
    }

    if (!preg_match("/^[\p{L} .'-]{2,80}$/u", $old["name"])) {
        $errors[] = "Enter a valid name.";
    }

    if (!preg_match("/^[a-zA-Z0-9_]{3,30}$/", $old["username"])) {
        $errors[] = "Username must be 3-30 letters, numbers or underscores.";
    }

    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email.";
    }

    if (!preg_match("/^[0-9]{10}$/", $old["mobile"])) {
        $errors[] = "Enter a 10-digit mobile number.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must contain at least 6 characters.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!in_array($old["course"], $courses, true)) {
        $errors[] = "Select a valid course.";
    }

    if (!in_array($old["year"], $years, true)) {
        $errors[] = "Select a valid year.";
    }

    if (!in_array($old["gender"], $genders, true)) {
        $errors[] = "Select a valid gender.";
    }

    if (!isset($_POST["terms"])) {
        $errors[] = "Accept the Terms and Conditions.";
    }

    if (!$errors) {
        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        if (!$check) {
            $errors[] = "Could not check existing accounts. Verify your database table.";
        } else {
            $check->bind_param("ss", $old["username"], $old["email"]);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errors[] = "Username or email already exists.";
            }

            $check->close();
        }
    }

    if (!$errors) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users
            (name, username, email, mobile, password, course, year, gender)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $errors[] = "Could not prepare registration. Check your users table columns.";
        } else {
            $stmt->bind_param(
                "ssssssss",
                $old["name"],
                $old["username"],
                $old["email"],
                $old["mobile"],
                $hashedPassword,
                $old["course"],
                $old["year"],
                $old["gender"]
            );

            if ($stmt->execute()) {
                $record = [
                    "name" => $old["name"],
                    "username" => $old["username"],
                    "email" => $old["email"],
                    "mobile" => $old["mobile"],
                    "course" => $old["course"],
                    "year" => $old["year"],
                    "gender" => $old["gender"],
                    "submitted_at" => date(DATE_ATOM)
                ];

                // Save to CSV
                $csvHandle = fopen($csvFile, "ab");

                if ($csvHandle !== false) {
                    if (flock($csvHandle, LOCK_EX)) {
                        $stats = fstat($csvHandle);

                        if ($stats["size"] === 0) {
                            fputcsv($csvHandle, array_keys($record));
                        }

                        fputcsv($csvHandle, array_values($record));
                        fflush($csvHandle);
                        flock($csvHandle, LOCK_UN);
                    }

                    fclose($csvHandle);
                }

                // Save to JSON
                $existing = [];

                if (is_file($jsonFile)) {
                    $content = file_get_contents($jsonFile);
                    $decoded = $content !== false
                        ? json_decode($content, true)
                        : null;

                    if (is_array($decoded)) {
                        $existing = $decoded;
                    }
                }

                $existing[] = $record;

                $json = json_encode(
                    $existing,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                );

                if ($json !== false) {
                    file_put_contents($jsonFile, $json, LOCK_EX);
                }

                $stmt->close();
                $conn->close();

                // Redirect to StudentHub login page
                header("Location: /xampp/studenthub/login.php?registered=1");
                exit();

            } else {
                $errors[] = "Registration failed. Username or email may already exist.";
            }

            $stmt->close();
        }
    }

    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registration</title>
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
        <li><a href="register.php">Register</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="dashboard.html">Dashboard</a></li>
    </ul>
</nav>

<main>
    <h2>Create Account</h2>

    <?php foreach ($errors as $error): ?>
        <p class="error" role="alert"><?= h($error) ?></p>
    <?php endforeach; ?>

    <form method="POST" action="register.php"
          onsubmit="return validateRegister()">

        <input type="hidden" name="csrf"
               value="<?= h($_SESSION["csrf"]) ?>">

        <label for="name">Full Name</label><br>
        <input id="name" name="name" maxlength="80" required
               value="<?= h($old["name"]) ?>">
        <br><br>

        <label for="username">Username</label><br>
        <input id="username" name="username" maxlength="30"
               pattern="[a-zA-Z0-9_]{3,30}" required
               value="<?= h($old["username"]) ?>">
        <br><br>

        <label for="email">Email</label><br>
        <input type="email" id="email" name="email"
               maxlength="190" required
               value="<?= h($old["email"]) ?>">
        <br><br>

        <label for="mobile">Mobile</label><br>
        <input type="text" id="mobile" name="mobile"
               maxlength="10" pattern="[0-9]{10}"
               inputmode="numeric" required
               value="<?= h($old["mobile"]) ?>">
        <br><br>

        <label for="password">Password</label><br>
        <input type="password" id="password" name="password"
               minlength="6" required>
        <br><br>

        <label for="confirm">Confirm Password</label><br>
        <input type="password" id="confirm" name="confirm"
               minlength="6" required>
        <br><br>

        <label for="course">Course</label><br>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= h($course) ?>"
                    <?= $old["course"] === $course ? "selected" : "" ?>>
                    <?= h($course) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label for="year">Year</label><br>
        <select id="year" name="year" required>
            <option value="">Select Year</option>
            <?php foreach ($years as $year): ?>
                <option value="<?= h($year) ?>"
                    <?= $old["year"] === $year ? "selected" : "" ?>>
                    <?= h($year) ?> Year
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Gender</label><br>

        <?php foreach ($genders as $gender): ?>
            <input type="radio" id="gender<?= h($gender) ?>"
                   name="gender" value="<?= h($gender) ?>"
                   required
                   <?= $old["gender"] === $gender ? "checked" : "" ?>>
            <label for="gender<?= h($gender) ?>"><?= h($gender) ?></label>
        <?php endforeach; ?>

        <br><br>

        <input type="checkbox" id="terms" name="terms"
               value="accepted" required>
        <label for="terms">I accept Terms and Conditions</label>

        <br><br>

        <button type="submit" name="register">Register</button>
    </form>

    <p><a href="studenthub.html">Go Back to Home</a></p>
</main>

<footer>
    <p>© 2026 Student Hub | Developed by Nihal</p>
</footer>

<script src="js/script.js"></script>
<script>
    if (typeof updateNavigation === "function") {
        updateNavigation();
    }
</script>

</body>
</html>