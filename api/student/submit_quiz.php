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

$quiz_id    = $data['quiz_id'] ?? '';
$student_id = $data['student_id'] ?? '';
$answers    = $data['answers'] ?? [];

if ($quiz_id === '' || $student_id === '' || empty($answers)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing data'
    ]);
    exit;
}

// 🔒 منع إعادة التسليم
$check = $conn->prepare("
    SELECT id FROM quiz_answers 
    WHERE quiz_id = ? AND student_id = ? 
    LIMIT 1
");
$check->bind_param("ii", $quiz_id, $student_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Quiz already submitted'
    ]);
    exit;
}
$check->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO quiz_answers 
        (quiz_id, question_id, student_id, selected_option)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($answers as $question_id => $selected_option) {
        $stmt->bind_param(
            "iiis",
            $quiz_id,
            $question_id,
            $student_id,
            $selected_option
        );
        $stmt->execute();
    }

    $stmt->close();
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Quiz submitted successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        'status' => 'error',
        'message' => 'Submission failed'
    ]);
}
?>
