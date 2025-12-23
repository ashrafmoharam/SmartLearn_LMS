<?php
// السماح بالوصول من أي مصدر (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// السماح بالـ preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// الاتصال بقاعدة البيانات
include '../db.php';

// قراءة quiz_id من GET
$quiz_id = $_GET['quiz_id'] ?? '';

if ($quiz_id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Quiz ID is missing',
        'results' => []
    ]);
    exit;
}

// استعلام لجلب نتائج الطلاب للكويز مع البريد الجامعي
$stmt = $conn->prepare(
    "SELECT s.id AS student_id, s.full_name AS student_name, s.university_email,
            qs.score, q.total_marks AS total_points, qs.submitted_at
     FROM students s
     LEFT JOIN quiz_submissions qs ON s.id = qs.student_id AND qs.quiz_id = ?
     LEFT JOIN quizzes q ON q.id = ?"
);

$stmt->bind_param("ii", $quiz_id, $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name'],
        'university_email' => $row['university_email'],
        'score' => $row['score'] ?? 0,
        'total_points' => $row['total_points'] ?? 0,
        'submitted_at' => $row['submitted_at'] ?? null
    ];
}

// إرجاع النتيجة
echo json_encode([
    'status' => 'success',
    'results' => $results
]);
?>
