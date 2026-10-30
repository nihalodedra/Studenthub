
<?php
session_start();

/* Check Login */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Check Valid Role */
$role = strtolower($_SESSION['role'] ?? '');

if (!in_array($role, ['admin', 'student', 'faculty'], true)) {
    http_response_code(403);
    exit("Access denied. Invalid role.");
}

/* Session Timeout: 5 Minutes */
if (
    isset($_SESSION['last_activity']) &&
    time() - $_SESSION['last_activity'] > 300
) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

/* Common User Details */
$username = $_SESSION['username'] ?? 'User';
$totalUsers = 0;
$adminEmail = '-';

/* Admin-only Database Details */
if ($role === 'admin') {
    $conn = mysqli_connect("localhost", "root", "", "secure_login");

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    /* Get Total Users */
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);
    $totalUsers = (int) $row['total'];

    /* Get Admin Email */
    $checkColumns = mysqli_query(
        $conn,
        "SHOW COLUMNS FROM users LIKE 'email'"
    );

    if ($checkColumns && mysqli_num_rows($checkColumns) > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT email FROM users WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);

            $emailResult = mysqli_stmt_get_result($stmt);
            $emailRow = mysqli_fetch_assoc($emailResult);

            if ($emailRow) {
                $adminEmail = $emailRow['email'] ?? '-';
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Hub - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .role-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            background: #263b80;
        }

        .role-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .role-menu a:hover {
            background: #4058a8;
        }

        .role-content {
            padding: 20px;
        }

        .role-label {
            display: inline-block;
            padding: 6px 12px;
            background: #e8edff;
            color: #263b80;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<header>
    <h1>Student Hub</h1>
    <p>Learn • Connect • Share • Grow</p>
</header>

<!-- Dynamic Menu -->
<nav class="role-menu">
    <a href="studenthub.html">Home</a>
    <a href="about.html">About</a>

    <?php if ($role === 'admin'): ?>

        <a href="admin.php">Admin Dashboard</a>
        <a href="events.php">Manage Events</a>
        <a href="student.php">Manage Students</a>
        <a href="feedback.html">Feedback</a>
        <a href="discussion.html">Discussion</a>

    <?php elseif ($role === 'student'): ?>

        <a href="dashboard.html">Dashboard</a>
        <a href="event.html">View Events</a>
        <a href="profile.html">My Profile</a>
        <a href="discussion.html">Discussion</a>

    <?php elseif ($role === 'faculty'): ?>

        <a href="faculty.php">Faculty Dashboard</a>
        <a href="event.html">View Events</a>
        <a href="discussion.html">Discussion</a>
        <a href="profile.html">My Profile</a>

    <?php endif; ?>

    <a href="logout.php">Logout</a>
</nav>

<section class="role-content">

    <h2>
        <?php echo ucfirst($role); ?> Dashboard
    </h2>

    <p>
        Welcome,
        <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!
    </p>

    <p class="role-label">
        Your Role: <?php echo htmlspecialchars(ucfirst($role)); ?>
    </p>

    <?php if ($role === 'admin'): ?>

        <!-- Admin Statistics -->
        <div class="admin-stats">

            <div class="admin-card">
                <h3>Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>

            <div class="admin-card">
                <h3>Events</h3>
                <p>4</p>
            </div>

            <div class="admin-card">
                <h3>Discussions</h3>
                <p>15</p>
            </div>

            <div class="admin-card">
                <h3>Notifications</h3>
                <p>3</p>
            </div>

        </div>

        <!-- Admin Actions -->
        <div class="admin-actions">

            <div class="admin-action-card">
                <h3>Manage Users</h3>
                <p>View and manage registered users.</p>
                <button type="button" onclick="manageUsers()">
                    Manage Users
                </button>
            </div>

            <div class="admin-action-card">
                <h3>Manage Events</h3>
                <p>Add, edit, delete events and upload posters.</p>
                <a href="events.php">
                    <button type="button">Open Event Manager</button>
                </a>
            </div>

            <div class="admin-action-card">
                <h3>Manage Discussions</h3>
                <p>Monitor student discussions.</p>
                <button type="button" onclick="manageDiscussions()">
                    Manage Discussions
                </button>
            </div>

            <div class="admin-action-card">
                <h3>Review Feedback</h3>
                <p>Check feedback submitted by students.</p>
                <button type="button" onclick="reviewFeedback()">
                    Review Feedback
                </button>
            </div>

            <div class="admin-action-card">
                <h3>Manage Students</h3>
                <p>Add, edit, delete, search, and filter students.</p>
                <a href="student.php">
                    <button type="button">Manage Students</button>
                </a>
            </div>

        </div>

        <!-- Admin Details -->
        <div class="user-management">
            <h2>Current Admin Details</h2>

            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($username); ?></td>
                        <td><?php echo htmlspecialchars($adminEmail); ?></td>
                        <td><?php echo htmlspecialchars($role); ?></td>
                        <td>Active</td>
                    </tr>
                </tbody>
            </table>
        </div>

    <?php elseif ($role === 'student'): ?>

        <h3>Student Services</h3>
        <p>Use the menu to view college events, discussions, and your profile.</p>

        <div class="admin-actions">
            <div class="admin-action-card">
                <h3>College Events</h3>
                <p>View upcoming college events.</p>
                <a href="event.html">View Events</a>
            </div>

            <div class="admin-action-card">
                <h3>My Profile</h3>
                <p>View your student profile.</p>
                <a href="profile.html">Open Profile</a>
            </div>
        </div>

    <?php elseif ($role === 'faculty'): ?>

        <h3>Faculty Services</h3>
        <p>Use the menu to access academic discussions and college events.</p>

        <div class="admin-actions">
            <div class="admin-action-card">
                <h3>College Events</h3>
                <p>View upcoming college events.</p>
                <a href="event.html">View Events</a>
            </div>

            <div class="admin-action-card">
                <h3>Discussions</h3>
                <p>Visit the college discussion page.</p>
                <a href="discussion.html">Open Discussions</a>
            </div>
        </div>

    <?php endif; ?>

    <div class="back-home">
        <a href="studenthub.html">Go Back to Home</a>
    </div>

</section>

<footer>
    <p>© 2026 Student Hub | Developed by Nihal</p>

    <p>
        <a href="https://www.facebook.com/"
           target="_blank" rel="noopener noreferrer">Facebook</a> |

        <a href="https://www.instagram.com/"
           target="_blank" rel="noopener noreferrer">Instagram</a> |

        <a href="https://www.linkedin.com/"
           target="_blank" rel="noopener noreferrer">LinkedIn</a>
    </p>
</footer>

<script src="js/script.js"></script>

</body>
</html>
