<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

$quiz_id = $data['quiz_id'] ?? '';
$student_id = $data['student_id'] ?? '';
$answers = $data['answers'] ?? [];

if ($quiz_id === '' || $student_id === '' || empty($answers)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

// إدراج كل إجابة في جدول quiz_submissions
foreach ($answers as $question_id => $selected_option) {
    $stmt = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, question_id, student_id, selected_option) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $quiz_id, $question_id, $student_id, $selected_option);
    $stmt->execute();
}

echo json_encode(['status' => 'success', 'message' => 'Quiz submitted successfully']);
?>
