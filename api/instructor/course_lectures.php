<?php
header("Access-Control-Allow-Origin: *"); // يسمح بأي دومين
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
include_once __DIR__ . '/../db.php';

// عنوان السيرفر أو الـ IP + مسار uploads
$base_url = "http://localhost/SmartLearn_LMS/uploads/lectures/"; 

$course_id = $_GET['course_id'] ?? '';
if (!$course_id) {
    echo json_encode(['status' => 'error', 'message' => 'Course ID is required']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM lectures WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$lectures = [];
while ($row = $result->fetch_assoc()) {
    $lectures[] = [
        'id' => $row['id'],
        'course_id' => $row['course_id'],
        'title' => $row['title'],
        'file_url' => $base_url . $row['pdf_file'], // الرابط المباشر للملف
        'created_at' => $row['created_at']
    ];
}

echo json_encode(['status' => 'success', 'lectures' => $lectures]);
?>
