<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION["role"] != "admin") {
    http_response_code(403);
    exit("Access denied. Admin only.");
}

$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];
$students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()["total"];
$events = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()["total"];
$registrations = $conn->query("SELECT COUNT(*) AS total FROM registrations")->fetch_assoc()["total"];

$stmt = $conn->prepare(
    "INSERT INTO audit_logs (user_id, action) VALUES (?, ?)"
);
$action = "Viewed admin dashboard";
$stmt->bind_param("is", $_SESSION["user_id"], $action);
$stmt->execute();

$latest = $conn->query(
    "SELECT r.id, u.name, e.title, r.registered_at
     FROM registrations r
     LEFT JOIN users u ON r.user_id = u.id
     LEFT JOIN events e ON r.event_id = e.id
     ORDER BY r.registered_at DESC LIMIT 5"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?= htmlspecialchars($_SESSION["name"]) ?></p>

    <nav class="mb-4">
        <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
        <a href="students.php" class="btn btn-secondary">Students</a>
        <a href="events.php" class="btn btn-secondary">Events</a>
        <a href="audit.php" class="btn btn-secondary">Audit Logs</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </nav>

    <div class="row g-3">
        <?php foreach ([
            "Users" => $users,
            "Students" => $students,
            "Events" => $events,
            "Registrations" => $registrations
        ] as $label => $count): ?>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm">
                    <h5><?= $label ?></h5>
                    <h2><?= $count ?></h2>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h4 class="mt-4">Latest Registrations</h4>

    <table class="table table-bordered bg-white">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Event</th>
            <th>Date</th>
        </tr>

        <?php while ($row = $latest->fetch_assoc()): ?>
            <tr>
                <td><?= $row["id"] ?></td>
                <td><?= htmlspecialchars($row["name"] ?? "Unknown") ?></td>
                <td><?= htmlspecialchars($row["title"] ?? "Unknown") ?></td>
                <td><?= htmlspecialchars($row["registered_at"]) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>


̥