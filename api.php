<?php
header("Content-Type: application/json");
require "db.php";

$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true) ?? [];

if ($method == "GET") {
    $result = $conn->query("SELECT * FROM students");
    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode($students);
}

elseif ($method == "POST") {
    $name = trim($data["name"] ?? "");
    $email = trim($data["email"] ?? "");

    if ($name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Enter valid name and email"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Student added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to add student"]);
    }
}

elseif ($method == "PUT") {
    $id = (int)($data["id"] ?? 0);
    $name = trim($data["name"] ?? "");
    $email = trim($data["email"] ?? "");

    if ($id < 1 || $name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Enter valid ID, name and email"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE students SET name=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $email, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Student updated successfully"]);
    } else {
        echo json_encode(["message" => "No changes made or student not found"]);
    }
}

elseif ($method == "DELETE") {
    $id = (int)($data["id"] ?? 0);

    if ($id < 1) {
        http_response_code(400);
        echo json_encode(["message" => "Enter a valid ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Student deleted successfully"]);
    } else {
        echo json_encode(["message" => "Student not found"]);
    }
}

else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
?>

