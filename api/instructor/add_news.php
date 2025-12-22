<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../db.php';

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$instructor_id = intval($_POST['instructor_id'] ?? 0);

if ($title === '' || $content === '' || $instructor_id === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO news (instructor_id, title, content, created_at)
     VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param("iss", $instructor_id, $title, $content);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'News added successfully'
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
    exit;
}
?>