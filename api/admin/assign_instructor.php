<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "../db.php";

$instructorId = $_POST['instructor_id'] ?? '';
$courseId = $_POST['course_id'] ?? '';

if (!$instructorId || !$courseId) {
    echo json_encode(["status" => "error", "message" => "Missing instructor_id or course_id"]);
    exit;
}

// تحديث الكورس لربط المدرس به
$stmt = $conn->prepare("UPDATE courses SET instructor_id = ? WHERE id = ?");
$stmt->bind_param("ii", $instructorId, $courseId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Instructor assigned to course successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
