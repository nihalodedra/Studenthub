<?php
session_start();

// Allow only admin users
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "college1");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$msg = "";
$editStudent = null;

// Delete student
if (isset($_POST["delete"])) {
    $id = (int)($_POST["delete_id"] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM students WHERE ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $msg = "Student deleted successfully!";
    } else {
        $msg = "Unable to delete student.";
    }

    mysqli_stmt_close($stmt);
}

// Add or update student
if (isset($_POST["save"])) {
    $id = (int)($_POST["id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");

    if ($name === "" || $email === "" || $course === "") {
        $msg = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Please enter a valid email address.";
    } else {
        if ($id > 0) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE students
                 SET name = ?, email = ?, course = ?
                 WHERE ID = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $name,
                $email,
                $course,
                $id
            );

            $successMessage = "Student updated successfully!";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO students (name, email, course)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $name,
                $email,
                $course
            );

            $successMessage = "Student added successfully!";
        }

        if (mysqli_stmt_execute($stmt)) {
            $msg = $successMessage;
        } else {
            $msg = "Error: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }
}

// Get student details for editing
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT ID, name, email, course
         FROM students
         WHERE ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $editStudent = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}

// Search and filter students
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");

$sql = "SELECT ID, name, email, course FROM students WHERE 1=1";
$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";

    $searchValue = "%" . $search . "%";

    $types .= "ss";
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($courseFilter !== "") {
    $sql .= " AND course = ?";

    $types .= "s";
    $params[] = $courseFilter;
}

$sql .= " ORDER BY ID DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$students = mysqli_stmt_get_result($stmt);

$courses = ["BCA", "BSc IT", "B.Tech AIML", "B.Tech IT", "MCA"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management | StudentHub</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <h1>StudentHub</h1>
    <p>Student Management System</p>

    <nav>
        <a href="studenthub.html">Home</a>
        <a href="admin.php">Admin Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

    <h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>

    <?php if ($msg !== ""): ?>
        <p><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form action="student.php" method="post">

        <input
            type="hidden"
            name="id"
            value="<?= htmlspecialchars((string)($editStudent["ID"] ?? "")) ?>"
        >

        <label for="name">Student Name:</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars($editStudent["name"] ?? "") ?>"
            required
        >

        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($editStudent["email"] ?? "") ?>"
            required
        >

        <label for="course">Course:</label>

        <select id="course" name="course" required>
            <option value="">Select Course</option>

            <?php foreach ($courses as $courseOption): ?>
                <option
                    value="<?= htmlspecialchars($courseOption) ?>"
                    <?= (($editStudent["course"] ?? "") === $courseOption)
                        ? "selected"
                        : "" ?>
                >
                    <?= htmlspecialchars($courseOption) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="save">
            <?= $editStudent ? "Update Student" : "Add Student" ?>
        </button>

        <?php if ($editStudent): ?>
            <a href="student.php">Cancel</a>
        <?php endif; ?>

    </form>

    <hr>

    <h2>Search and Filter Students</h2>

    <form action="student.php" method="get">

        <label for="search">Search by name or email:</label>
        <input
            type="text"
            id="search"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Enter name or email"
        >

        <label for="filterCourse">Filter by Course:</label>

        <select id="filterCourse" name="course">
            <option value="">All Courses</option>

            <?php foreach ($courses as $courseOption): ?>
                <option
                    value="<?= htmlspecialchars($courseOption) ?>"
                    <?= $courseFilter === $courseOption ? "selected" : "" ?>
                >
                    <?= htmlspecialchars($courseOption) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
        <a href="student.php">Clear</a>

    </form>

    <hr>

    <h2>Student Records</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        <?php if (mysqli_num_rows($students) > 0): ?>

            <?php while ($student = mysqli_fetch_assoc($students)): ?>

                <tr>
                    <td>
                        <?= htmlspecialchars((string)$student["ID"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student["name"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student["email"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student["course"]) ?>
                    </td>

                    <td>
                        <a href="student.php?edit=<?= (int)$student["ID"] ?>">
                            Edit
                        </a>

                        <form action="student.php" method="post">
                            <input
                                type="hidden"
                                name="delete_id"
                                value="<?= (int)$student["ID"] ?>"
                            >

                            <button
                                type="submit"
                                name="delete"
                                value="1"
                                onclick="return confirm('Delete this student?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="5">No students found.</td>
            </tr>

        <?php endif; ?>

        </tbody>
    </table>

</main>

<footer>
    <p>&copy; <?= date("Y") ?> StudentHub</p>
</footer>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>