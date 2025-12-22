<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/PHPMailer/PHPMailer.php';
require '../../vendor/PHPMailer/SMTP.php';
require '../../vendor/PHPMailer/Exception.php';
include "../db.php";

// استلام البيانات
$name = $_POST['name'] ?? null;
$personalEmail = $_POST['personal_email'] ?? null;
$role = $_POST['role'] ?? null;

if (!$name || !$personalEmail || !$role) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

// التأكد من أن البريد الشخصي غير موجود مسبقاً في أي جدول
$tables = ['admins' => 'personal_email', 'instructors' => 'personal_email', 'students' => 'personal_email'];
foreach ($tables as $table => $column) {
    $stmt = $conn->prepare("SELECT id FROM $table WHERE $column=?");
    $stmt->bind_param("s", $personalEmail);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status"=>"error","message"=>"Email already exists"]);
        exit;
    }
}

// تسجيل الطلاب فقط
if ($role !== 'student') {
    echo json_encode(["status"=>"error","message"=>"Invalid role"]);
    exit;
}

// إنشاء البريد الجامعي
$firstName = strtolower(explode(" ", $name)[0]);
$year = date("Y");
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM students");
$row = mysqli_fetch_assoc($result);
$serial = str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);
$univEmail = "$firstName.$year$serial@student.eng.edu";

// كلمة المرور العشوائية
$defaultPassword = bin2hex(random_bytes(4)); // 8 أحرف عشوائية
$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

// إدخال الطالب في قاعدة البيانات
$stmt = $conn->prepare("INSERT INTO students (full_name, personal_email, university_email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $personalEmail, $univEmail, $hashedPassword);

if ($stmt->execute()) {
    // إرسال البريد الشخصي باستخدام PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'generalmoharam5@gmail.com'; // بريدك
        $mail->Password = 'cdls qvsm gsxk juee';       // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('generalmoharam5@gmail.com', 'SmartLearn LMS');
        $mail->addAddress($personalEmail, $name);

        $mail->isHTML(true);
        $mail->Subject = 'SmartLearn LMS Account Created';
        $mail->Body = "
            <h3>Account Created Successfully!</h3>
            <p>Hello <b>$name</b>,</p>
            <p>Your LMS student account has been created.</p>
            <p><b>Email:</b> $univEmail<br>
               <b>Password:</b> $defaultPassword</p>
            <p>Please change your password after first login.</p>
        ";

        $mail->send();

        echo json_encode([
            "status" => "success",
            "university_email" => $univEmail,
            "password" => $defaultPassword,
            "message" => "Student account created and email sent!"
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "success",
            "university_email" => $univEmail,
            "password" => $defaultPassword,
            "message" => "Account created but failed to send email: {$mail->ErrorInfo}"
        ]);
    }

} else {
    echo json_encode(["status"=>"error","message"=>"Failed to create student: ".$stmt->error]);
}
