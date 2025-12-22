<?php
header("Access-Control-Allow-Origin: *"); // يسمح بأي دومين
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
include_once __DIR__ . '/../db.php';


$course_id = $_GET['course_id'] ?? '';

if (!$course_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID is required'
    ]);
    exit;
}

// جلب بيانات الطلاب المسجلين في الكورس
$stmt = $conn->prepare("
    SELECT s.id, s.full_name, s.personal_email, s.university_email
    FROM student_courses sc
    INNER JOIN students s ON sc.student_id = s.id
    WHERE sc.course_id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    'status' => 'success',
    'students' => $students
]);
?>
