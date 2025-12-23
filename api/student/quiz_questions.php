<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../db.php';

$quiz_id = $_GET['quiz_id'] ?? '';

if ($quiz_id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Quiz ID is missing',
        'questions' => []
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM quiz_questions WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['id'],
        'question_text' => $row['question_text'],
        'options' => [
            'A' => $row['option_a'],
            'B' => $row['option_b'],
            'C' => $row['option_c'],
            'D' => $row['option_d'],
        ]
    ];
}

echo json_encode([
    'status' => 'success',
    'questions' => $questions
]);
?>
