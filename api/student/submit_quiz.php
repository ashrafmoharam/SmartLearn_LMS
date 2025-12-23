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

error_reporting(0);
ini_set('display_errors', 0);

$data = json_decode(file_get_contents("php://input"), true);

$quiz_id    = $data['quiz_id'] ?? '';
$student_id = $data['student_id'] ?? '';
$answers    = $data['answers'] ?? [];

if ($quiz_id === '' || $student_id === '' || empty($answers)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

// منع إرسال الكويز مرتين
$check = $conn->prepare("SELECT id FROM quiz_submissions WHERE quiz_id=? AND student_id=? LIMIT 1");
$check->bind_param("ii", $quiz_id, $student_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Quiz already submitted']);
    exit;
}
$check->close();

// Transaction
$conn->begin_transaction();

try {
    $total_score = 0;

    // إدخال كل إجابة في quiz_answers مع مقارنة correct_option
    $stmt = $conn->prepare("SELECT correct_option, marks FROM quiz_questions WHERE id=? AND quiz_id=?");

    $insertAnswer = $conn->prepare("
        INSERT INTO quiz_answers(quiz_id, question_id, student_id, selected_option, is_correct, submitted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    foreach ($answers as $question_id => $selected_option) {
        $selected_option = strtoupper($selected_option);

        $stmt->bind_param("ii", $question_id, $quiz_id);
        $stmt->execute();
        $stmt->bind_result($correct_option, $marks);
        if ($stmt->fetch()) {
            $is_correct = ($selected_option === strtoupper($correct_option)) ? strtoupper($correct_option) : 'X';
            if ($is_correct !== 'X') $total_score += $marks;

            $insertAnswer->bind_param(
                "iiiss",
                $quiz_id,
                $question_id,
                $student_id,
                $selected_option,
                $is_correct
            );
            $insertAnswer->execute();
        }
        $stmt->free_result();
    }

    $stmt->close();
    $insertAnswer->close();

    // إدخال النتيجة في quiz_submissions
    $stmt2 = $conn->prepare("INSERT INTO quiz_submissions (quiz_id, student_id, score, submitted_at) VALUES (?, ?, ?, NOW())");
    $stmt2->bind_param("iii", $quiz_id, $student_id, $total_score);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Quiz submitted successfully',
        'score' => $total_score
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Submission failed',
        'error' => $e->getMessage()
    ]);
}
?>
