<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// الاتصال بقاعدة البيانات
include_once __DIR__ . "/../db.php";

// قراءة student_id من GET
$student_id = $_GET['student_id'] ?? '';

if (empty($student_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Student ID is required"
    ]);
    exit;
}

// استعلام لجلب جميع الكويزات ودرجات الطالب
$stmt = $conn->prepare(
    "SELECT qs.id, qs.quiz_id, qs.score, qs.submitted_at, q.title AS quiz_title
     FROM quiz_submissions qs
     JOIN quizzes q ON qs.quiz_id = q.id
     WHERE qs.student_id = ?
     ORDER BY qs.submitted_at DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

echo json_encode([
    "status" => "success",
    "grades" => $grades
]);
?>
