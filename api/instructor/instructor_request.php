<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php';

// تحقق من البيانات
$fullName = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['personal_email'] ?? '';
$field = $_POST['field'] ?? '';

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != 0) {
    echo json_encode(["status" => "error", "message" => "CV file not uploaded"]);
    exit;
}

$cvFile = $_FILES['cv'];
$cvName = time() . "_" . basename($cvFile['name']);
$targetDir = __DIR__ . "/uploads/";
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
$targetFile = $targetDir . $cvName;

if (!move_uploaded_file($cvFile['tmp_name'], $targetFile)) {
    echo json_encode(["status" => "error", "message" => "Failed to save CV file"]);
    exit;
}

// إدخال البيانات في قاعدة البيانات
$stmt = $pdo->prepare("INSERT INTO instructor_requests (full_name, phone, personal_email, field, cv) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$fullName, $phone, $email, $field, $cvName]);

echo json_encode(["status" => "success", "message" => "Request submitted successfully"]);
?>
