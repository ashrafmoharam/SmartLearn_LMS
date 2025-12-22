<?php

// السماح بالوصول من أي مصدر (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// السماح بالـ preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}



// الاتصال بقاعدة البيانات
include '../db.php';

// قراءة البيانات من POST
$course_id = $_POST['course_id'] ?? '';
$title     = trim($_POST['title'] ?? '');

// التحقق من البيانات المطلوبة
if ($course_id === '' || $title === '' || empty($_FILES['file'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Course ID, title, or file is missing'
    ]);
    exit;
}

$file = $_FILES['file'];

// التحقق من أي خطأ أثناء رفع الملف
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'error',
        'message' => 'File upload error'
    ]);
    exit;
}

// التأكد من أن الملف PDF فقط
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only PDF files are allowed'
    ]);
    exit;
}

// مجلد حفظ الملفات
$uploadDir = '../../uploads/lectures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// إنشاء اسم فريد للملف لتجنب التكرار
$newFileName = time() . '_' . uniqid() . '.pdf';
$destination = $uploadDir . $newFileName;

// نقل الملف للمجلد
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to move uploaded file'
    ]);
    exit;
}

// حفظ بيانات المحاضرة في قاعدة البيانات
$stmt = $conn->prepare(
    "INSERT INTO lectures (course_id, title, pdf_file, created_at)
     VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param("iss", $course_id, $title, $newFileName);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Lecture uploaded successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}
?>
