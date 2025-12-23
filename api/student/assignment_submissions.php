<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "../db.php"; // الاتصال بقاعدة البيانات

$course_id = $_POST['course_id'] ?? '';
$assignment_id = $_POST['assignment_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';

if (!$course_id || !$assignment_id || !$student_id || !isset($_FILES['file'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$file = $_FILES['file'];
$uploadDir = '../../uploads/assignment_submissions/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$filename = time() . '_' . basename($file['name']);
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // حفظ بيانات الرفع في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submission_file, submitted_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $assignment_id, $student_id, $filename);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insert failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
}
?>
