<?php
session_start();
require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, name, password, role FROM users WHERE email=?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        $stmt = $conn->prepare(
            "INSERT INTO audit_logs (user_id, action) VALUES (?, ?)"
        );
        $action = "Logged in";
        $stmt->bind_param("is", $user["id"], $action);
        $stmt->execute();

        if ($user["role"] == "admin") {
            header("Location: dashboard.php");
        } else {
            header("Location: user.php");
        }
        exit;
    } else {
        $message = "Invalid email or password";
    }
}
?>

<h2>Login</h2>

<form method="post">
    <input type="email" name="email" placeholder="Enter email" required>
    <br><br>
    <input type="password" name="password" placeholder="Enter password" required>
    <br><br>
    <button type="submit">Login</button>
</form>

<p><?= htmlspecialchars($message) ?></p>    