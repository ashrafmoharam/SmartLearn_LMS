<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

include "../db.php";

// قراءة البيانات المرسلة من الـ POST
$data = json_decode(file_get_contents("php://input"), true);

$course_id = $data['course_id'] ?? '';
$title = $data['title'] ?? '';
$total_marks = $data['totalMarks'] ?? 0;
$questions = $data['questions'] ?? [];

if (!$course_id || !$title || $total_marks <= 0 || empty($questions)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// إضافة الكويز إلى جدول quizzes
$stmt = $conn->prepare("INSERT INTO quizzes (course_id, title, total_marks, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("isi", $course_id, $title, $total_marks);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create quiz']);
    exit;
}

$quiz_id = $stmt->insert_id;

// إضافة الأسئلة إلى جدول quiz_questions
$question_stmt = $conn->prepare(
    "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)"
);

foreach ($questions as $q) {
    $question_stmt->bind_param(
        "issssss",
        $quiz_id,
        $q['question'],
        $q['options'][0],
        $q['options'][1],
        $q['options'][2],
        $q['options'][3],
        $q['answer']
    );
    $question_stmt->execute();
}

echo json_encode(['status' => 'success', 'quiz_id' => $quiz_id]);
?>
