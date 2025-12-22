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

// قراءة course_id من GET
$course_id = $_GET['course_id'] ?? '';

if ($course_id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID is missing',
        'assignments' => []
    ]);
    exit;
}

// استعلام لجلب جميع الاسايمنتس للكورس
$stmt = $conn->prepare(
    "SELECT id, title, due_date, pdf_file, created_at 
     FROM assignments 
     WHERE course_id = ? 
     ORDER BY created_at DESC"
);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'due_date' => $row['due_date'], // YYYY-MM-DD HH:MM:SS
        'pdf_file' => $row['pdf_file'],
        'created_at' => $row['created_at'],
    ];
}

// إرجاع النتيجة
echo json_encode([
    'status' => 'success',
    'assignments' => $assignments
]);
?>
