
<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

$msg = "";
$editEvent = null;

$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        die("Unable to create uploads folder.");
    }
}

function cleanText($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function removePoster($filename) {
    $filename = basename((string)$filename);

    if ($filename !== "" &&
        is_file(__DIR__ . "/uploads/" . $filename)) {
        unlink(__DIR__ . "/uploads/" . $filename);
    }
}

// Delete event
if (isset($_POST["delete"])) {
    $id = (int)($_POST["id"] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT poster FROM events WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($event) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM events WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            removePoster($event["poster"]);
            $msg = "Event deleted successfully!";
        } else {
            $msg = "Unable to delete event.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $msg = "Event not found.";
    }
}

// Add or update event
if (isset($_POST["save"])) {
    $id = (int)($_POST["id"] ?? 0);
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $location = trim($_POST["location"] ?? "");

    $oldPoster = "";

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT poster FROM events WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $existingEvent = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$existingEvent) {
            $msg = "Event not found.";
        } else {
            $oldPoster = $existingEvent["poster"] ?? "";
        }
    }

    if ($msg === "") {
        if ($title === "" || $description === "" ||
            $eventDate === "" || $location === "") {
            $msg = "Please fill in all event fields.";
        } else {
            $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);

            if (!$dateObject || $dateObject->format("Y-m-d") !== $eventDate) {
                $msg = "Please enter a valid event date.";
            }
        }
    }

    $newPoster = "";
    $posterUploaded = false;

    // Validate and upload poster
    if ($msg === "" &&
        isset($_FILES["poster"]) &&
        $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES["poster"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $msg = "Poster upload failed. Please try again.";
        } elseif ($file["size"] > 2 * 1024 * 1024) {
            $msg = "Poster must be 2 MB or smaller.";
        } elseif (!is_uploaded_file($file["tmp_name"])) {
            $msg = "Invalid uploaded file.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file["tmp_name"]);

            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/gif" => "gif",
                "image/webp" => "webp"
            ];

            if (!isset($allowedTypes[$mime]) ||
                @getimagesize($file["tmp_name"]) === false) {
                $msg = "Only valid JPG, PNG, GIF or WEBP images are allowed.";
            } else {
                $newPoster = bin2hex(random_bytes(16)) .
                    "." . $allowedTypes[$mime];

                if (move_uploaded_file(
                    $file["tmp_name"],
                    $uploadDir . $newPoster
                )) {
                    $posterUploaded = true;
                } else {
                    $msg = "Unable to save poster. Check the uploads folder.";
                }
            }
        }
    }

    if ($msg === "") {
        if ($id > 0) {
            if ($posterUploaded) {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE events
                     SET title = ?, description = ?, event_date = ?,
                         location = ?, poster = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssi",
                    $title,
                    $description,
                    $eventDate,
                    $location,
                    $newPoster,
                    $id
                );
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE events
                     SET title = ?, description = ?, event_date = ?,
                         location = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssi",
                    $title,
                    $description,
                    $eventDate,
                    $location,
                    $id
                );
            }

            if (mysqli_stmt_execute($stmt)) {
                if ($posterUploaded) {
                    removePoster($oldPoster);
                }

                $msg = "Event updated successfully!";
            } else {
                $msg = "Unable to update event.";
                if ($posterUploaded) {
                    removePoster($newPoster);
                }
            }

            mysqli_stmt_close($stmt);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO events
                 (title, description, event_date, location, poster)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $posterValue = $posterUploaded ? $newPoster : null;

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $title,
                $description,
                $eventDate,
                $location,
                $posterValue
            );

            if (mysqli_stmt_execute($stmt)) {
                $msg = "Event added successfully!";
            } else {
                $msg = "Unable to add event.";
                if ($posterUploaded) {
                    removePoster($newPoster);
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Load event for editing
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM events WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $editEvent = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}

// Search events
$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $searchValue = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM events
         WHERE title LIKE ? OR location LIKE ?
         ORDER BY event_date DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $searchValue,
        $searchValue
    );
} else {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM events ORDER BY event_date DESC"
    );
}

mysqli_stmt_execute($stmt);
$events = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | StudentHub</title>
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
        <li><a href="admin.php">Admin Dashboard</a></li>
        <li><a href="events.php">Manage Events</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <h1>Event Management</h1>

    <?php if ($msg !== ""): ?>
        <p><?= cleanText($msg) ?></p>
    <?php endif; ?>

    <section>
        <h2><?= $editEvent ? "Edit Event" : "Add New Event" ?></h2>

        <form action="events.php" method="post" enctype="multipart/form-data">
            <input
                type="hidden"
                name="id"
                value="<?= (int)($editEvent["id"] ?? 0) ?>"
            >

            <label for="title">Event Title</label><br>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="150"
                value="<?= cleanText($editEvent["title"] ?? "") ?>"
                required
            ><br>

            <label for="description">Description</label><br>
            <textarea
                id="description"
                name="description"
                required
            ><?= cleanText($editEvent["description"] ?? "") ?></textarea><br>

            <label for="event_date">Event Date</label><br>
            <input
                type="date"
                id="event_date"
                name="event_date"
                value="<?= cleanText($editEvent["event_date"] ?? "") ?>"
                required
            ><br>

            <label for="location">Location</label><br>
            <input
                type="text"
                id="location"
                name="location"
                maxlength="200"
                value="<?= cleanText($editEvent["location"] ?? "") ?>"
                required
            ><br>

            <label for="poster">Event Poster (JPG, PNG, GIF or WEBP; max 2 MB)</label><br>
            <input
                type="file"
                id="poster"
                name="poster"
                accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
            ><br>

            <?php if (!empty($editEvent["poster"])): ?>
                <p>Current poster:</p>
                <img
                    src="<?= "uploads/" . rawurlencode(basename($editEvent["poster"])) ?>"
                    alt="Current event poster"
                    width="150"
                ><br>
                <p>Leave the poster field empty to keep the current poster.</p>
            <?php endif; ?>

            <button type="submit" name="save">
                <?= $editEvent ? "Update Event" : "Add Event" ?>
            </button>

            <?php if ($editEvent): ?>
                <a href="events.php">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <hr>

    <section>
        <h2>Search Events</h2>

        <form action="events.php" method="get">
            <input
                type="text"
                name="search"
                placeholder="Search title or location"
                value="<?= cleanText($search) ?>"
            >
            <button type="submit">Search</button>
            <a href="events.php">Clear</a>
        </form>
    </section>

    <hr>

    <section>
        <h2>All Events</h2>

        <?php if (mysqli_num_rows($events) > 0): ?>
            <?php while ($event = mysqli_fetch_assoc($events)): ?>
                <article class="event">
                    <h3><?= cleanText($event["title"]) ?></h3>

                    <?php if (!empty($event["poster"])): ?>
                        <img
                            src="<?= "uploads/" . rawurlencode(basename($event["poster"])) ?>"
                            alt="Event poster"
                            width="180"
                        ><br>
                    <?php endif; ?>

                    <p><?= nl2br(cleanText($event["description"])) ?></p>
                    <p><strong>Date:</strong> <?= cleanText($event["event_date"]) ?></p>
                    <p><strong>Location:</strong> <?= cleanText($event["location"]) ?></p>

                    <a href="events.php?edit=<?= (int)$event["id"] ?>">Edit</a>

                    <form action="events.php" method="post"
                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$event["id"] ?>"
                        >
                        <button type="submit" name="delete" value="1">
                            Delete
                        </button>
                    </form>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </section>

    <p><a href="admin.php">Back to Admin Dashboard</a></p>
    <p><a href="studenthub.html">Back to Home</a></p>
</main>

<footer>
    <p>&copy; 2026 Student Hub | Developed by Nihal</p>
</footer>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
