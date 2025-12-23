<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// الاتصال بقاعدة البيانات
include '../db.php';

// قراءة البيانات من JSON
$data = json_decode(file_get_contents("php://input"), true);

$course_id = $data['course_id'] ?? '';
$title = $data['title'] ?? '';
$total_marks = $data['total_marks'] ?? 0;
$questions = $data['questions'] ?? [];

if (!$course_id || !$title || $total_marks <= 0 || empty($questions)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// إنشاء الكويز
$stmt = $conn->prepare("INSERT INTO quizzes (course_id, title, total_marks, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("isi", $course_id, $title, $total_marks);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create quiz']);
    exit;
}

$quiz_id = $stmt->insert_id;

// إدخال الأسئلة
foreach ($questions as $q) {
    $question_text = $q['question'] ?? '';
    $options = $q['options'] ?? ["", "", "", ""];
    $answer = $q['answer'] ?? '';

    $stmtQ = $conn->prepare(
        "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmtQ->bind_param(
        "issssss",
        $quiz_id,
        $question_text,
        $options[0],
        $options[1],
        $options[2],
        $options[3],
        $answer
    );
    $stmtQ->execute();
}

echo json_encode(['status' => 'success', 'quiz_id' => $quiz_id]);
?>
