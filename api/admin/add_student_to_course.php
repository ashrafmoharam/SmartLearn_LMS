<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "../db.php";

$studentId = $_POST['student_id'] ?? '';
$courseId = $_POST['course_id'] ?? '';

if (!$studentId || !$courseId) {
    echo json_encode(["status" => "error", "message" => "Missing student_id or course_id"]);
    exit;
}

// نضيف الطالب للكورس في جدول student_courses
$stmt = $conn->prepare("INSERT INTO student_courses (course_id, student_id) VALUES (?, ?)");
$stmt->bind_param("ii", $courseId, $studentId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Student added to course successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
