<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../db.php';

$course_id = $_POST['course_id'] ?? '';
$title     = trim($_POST['title'] ?? '');
$due_date  = $_POST['due_date'] ?? ''; // YYYY-MM-DD HH:MM:SS

if ($course_id === '' || $title === '' || $due_date === '' || empty($_FILES['file'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID, title, due date, or file is missing'
    ]);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'error',
        'message' => 'File upload error'
    ]);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only PDF files are allowed'
    ]);
    exit;
}

$uploadDir = '../../uploads/assignments/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$newFileName = time() . '_' . uniqid() . '.pdf';
$destination = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to move uploaded file'
    ]);
    exit;
}

// تخزين البيانات في قاعدة البيانات
$stmt = $conn->prepare(
    "INSERT INTO assignments (course_id, title, pdf_file, due_date, created_at)
     VALUES (?, ?, ?, ?, NOW())"
);
$stmt->bind_param("isss", $course_id, $title, $newFileName, $due_date);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Assignment uploaded successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}
?>
