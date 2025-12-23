<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

include "../db.php"; // ملف الاتصال بقاعدة البيانات

// التحقق من الحقول والملف
if(!isset($_FILES['cv_file']) || !isset($_POST['full_name']) || !isset($_POST['personal_email']) || !isset($_POST['phone'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields or CV file']);
    exit;
}

$cv_file = $_FILES['cv_file'];
$full_name = $_POST['full_name'];
$personal_email = $_POST['personal_email'];
$phone = $_POST['phone'];

// حفظ الملف على السيرفر
$target_dir = "../../uploads/cvs/";
if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);

$target_file = $target_dir . basename($cv_file["name"]);

if(move_uploaded_file($cv_file["tmp_name"], $target_file)) {
    // تسجيل البيانات في جدول instructor_requests
    $stmt = $conn->prepare("INSERT INTO instructor_requests (full_name, personal_email, phone, cv_file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $personal_email, $phone, $cv_file["name"]);

    if($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'CV and data uploaded successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: '.$stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to upload CV']);
}

$conn->close();
