<?php
include "../db.php";

// =========================================
// التحقق من البيانات المرسلة
// =========================================
$fullName = $_POST['full_name'] ?? '';
$phone    = $_POST['phone'] ?? '';
$email    = $_POST['personal_email'] ?? '';
$field    = $_POST['field'] ?? '';

if (!$fullName || !$phone || !$email || !$field || !isset($_FILES['cv'])) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields including CV are required"
    ]);
    exit;
}

// =========================================
// رفع الملف
// =========================================
$uploadDir = "../../uploads/cvs/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$cvName = time() . "_" . basename($_FILES['cv']['name']);
$targetFile = $uploadDir . $cvName;

if (!move_uploaded_file($_FILES['cv']['tmp_name'], $targetFile)) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to upload CV"
    ]);
    exit;
}

// =========================================
// إدخال البيانات في جدول instructor_requests
// =========================================
$stmt = $pdo->prepare(
    "INSERT INTO instructor_requests (full_name, phone, personal_email, field, cv, status)
     VALUES (?, ?, ?, ?, ?, 'pending')"
);
$stmt->execute([$fullName, $phone, $email, $field, $cvName]);

echo json_encode([
    "status" => "success",
    "message" => "Request submitted successfully"
]);
