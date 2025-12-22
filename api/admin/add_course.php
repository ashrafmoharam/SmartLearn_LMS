<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "../db.php";

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';

if (!$title || !$description) {
    echo json_encode(["status" => "error", "message" => "Missing title or description"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
$stmt->bind_param("ss", $title, $description);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Course added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
