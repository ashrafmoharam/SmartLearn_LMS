<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// السماح بالـ preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../db.php';

$course_id = $_GET['course_id'] ?? '';

if ($course_id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID is missing',
        'quizzes' => []
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, title, created_at FROM quizzes WHERE course_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'created_at' => $row['created_at'],
    ];
}

echo json_encode([
    'status' => 'success',
    'quizzes' => $quizzes
]);
?>
