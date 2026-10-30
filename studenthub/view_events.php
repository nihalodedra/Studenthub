<?php
header("Content-Type: application/json");

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "studenthub_events"
);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "SELECT id, title, description, event_date, location, poster
        FROM events
        ORDER BY event_date ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Could not load events"]);
    exit();
}

$events = [];

while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
}

echo json_encode($events);

mysqli_close($conn);
?>