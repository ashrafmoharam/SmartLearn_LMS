<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

include "../db.php"; // تأكد أن db.php موجود في نفس المسار

require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// استلام البريد الإلكتروني
$email = $_POST['email'] ?? '';

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Email required"]);
    exit;
}

// البحث في كل جدول مستخدمين
$tables = ['admins', 'instructors', 'students'];
$user = null;
$role = null;

foreach ($tables as $table) {
    $stmt = mysqli_prepare($conn, "SELECT id, full_name FROM $table WHERE personal_email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    if ($user) {
        $role = $table;
        break;
    }
}

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    exit;
}

// إنشاء كلمة مرور مؤقتة عشوائية
$defaultPassword = bin2hex(random_bytes(4)); // 8 أحرف عشوائية
$hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);

// تحديث كلمة المرور في الجدول المناسب
$stmt = mysqli_prepare($conn, "UPDATE $role SET password=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "si", $hashed, $user['id']);
mysqli_stmt_execute($stmt);

// إرسال البريد باستخدام PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'generalmoharam5@gmail.com'; // بريدك
    $mail->Password   = 'cdls qvsm gsxk juee';       // App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('generalmoharam5@gmail.com', 'SmartLearn LMS');
    $mail->addAddress($email, $user['full_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Reset LMS Password';
    $mail->Body    = "Hello {$user['full_name']},<br>Your new password: <b>$defaultPassword</b><br>Please change it after login.";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Password reset email sent"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send email: {$mail->ErrorInfo}"
    ]);
}
?>
