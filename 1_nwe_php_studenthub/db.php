<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "studenthub_events"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>