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

// استعلام لجلب الكورسات التي سجل فيها الطالب
$stmt = $conn->prepare(
    "SELECT c.id, c.title, c.description, c.created_at
     FROM courses c
     JOIN student_courses cs ON c.id = cs.course_id
     WHERE cs.student_id = ?
     ORDER BY c.created_at DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// إرجاع JSON للـ Flutter
echo json_encode([
    "status" => "success",
    "courses" => $courses
]);
