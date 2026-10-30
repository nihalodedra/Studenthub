<?php
session_start();
header("Content-Type: application/json");

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in as an admin."
    ]);
    exit;
}

require "db13.php";

$input = json_decode(file_get_contents("php://input"), true);
$action = $_GET["action"] ?? ($input["action"] ?? "list");

if ($action === "list") {
    $result = mysqli_query(
        $conn,
        "SELECT * FROM students ORDER BY id DESC"
    );

    $students = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }

    echo json_encode([
        "success" => true,
        "students" => $students
    ]);
} elseif ($action === "add" || $action === "update") {
    $name = trim($input["name"] ?? "");
    $email = trim($input["email"] ?? "");
    $course = trim($input["course"] ?? "");

    if ($name === "" || $email === "" || $course === "") {
        echo json_encode([
            "success" => false,
            "message" => "Please fill in all fields."
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "success" => false,
            "message" => "Enter a valid email address."
        ]);
        exit;
    }

    if ($action === "add") {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO students (name, email, course) VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $course);
        $done = mysqli_stmt_execute($stmt);
    } else {
        $id = intval($input["id"] ?? 0);

        if ($id <= 0) {
            echo json_encode([
                "success" => false,
                "message" => "Invalid student ID."
            ]);
            exit;
        }

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE students SET name = ?, email = ?, course = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $course, $id);
        $done = mysqli_stmt_execute($stmt);
    }

    echo json_encode([
        "success" => $done,
        "message" => $done
            ? ($action === "add" ? "Student added successfully." : "Student updated successfully.")
            : "Database operation failed."
    ]);
} elseif ($action === "delete") {
    $id = intval($input["id"] ?? 0);

    if ($id <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid student ID."
        ]);
        exit;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $done = mysqli_stmt_execute($stmt);

    echo json_encode([
        "success" => $done,
        "message" => $done
            ? "Student deleted successfully."
            : "Could not delete student."
    ]);
} else {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid action."
    ]);
}

mysqli_close($conn);
?>