<?php
header("Content-Type: application/json; charset=UTF-8");
include "../db.php";

require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$requestId = $_POST['id'] ?? '';
if (!$requestId) {
    echo json_encode(['status' => 'error', 'message' => 'Request ID is required']);
    exit;
}

// جلب بيانات الطلب
$stmt = $conn->prepare("SELECT * FROM instructor_requests WHERE id = ?");
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Request not found']);
    exit;
}

$request = $result->fetch_assoc();

// توليد البريد الجامعي
$nameParts = explode(' ', strtolower($request['full_name']));
$firstName = $nameParts[0];
$year = date('Y');

// الرقم التسلسلي 4 أرقام
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM instructors");
$row = $totalResult->fetch_assoc();
$serial = str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);

$universityEmail = $firstName . '.' . $year . $serial . '@instructor.eng.edu';

// توليد كلمة مرور عشوائية مؤقتة
$password = bin2hex(random_bytes(4)); // 8 أحرف
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// إضافة المدرس إلى جدول instructors
$insert = $conn->prepare("INSERT INTO instructors (full_name, personal_email, university_email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
$insert->bind_param("ssss", $request['full_name'], $request['personal_email'], $universityEmail, $hashedPassword);

if ($insert->execute()) {
    // تحديث حالة الطلب
    $conn->query("UPDATE instructor_requests SET status='approved' WHERE id='$requestId'");

    // إرسال البريد الشخصي
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // ضع SMTP server الخاص بك
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com'; // البريد المرسل
        $mail->Password = 'your-email-password';    // كلمة المرور
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your-email@example.com', 'SmartLearn LMS');
        $mail->addAddress($request['personal_email'], $request['full_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Your Instructor Account Approved';
        $mail->Body = "Hello {$request['full_name']},<br><br>
                       Your account has been approved!<br>
                       University Email: <b>{$universityEmail}</b><br>
                       Password: <b>{$password}</b><br><br>
                       You can now log in using your university email and this password.<br><br>
                       Regards,<br>SmartLearn LMS";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Instructor approved and email sent']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Instructor approved but email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to approve instructor']);
}
?>
