<?php
header("Content-Type: application/json; charset=UTF-8");
include "../db.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$requestId = $_POST['id'] ?? '';

if (!$requestId) {
    echo json_encode(['status' => 'error', 'message' => 'Request ID is required']);
    exit;
}

// جلب بيانات الطلب
$query = mysqli_query($conn, "SELECT * FROM instructor_requests WHERE id='$requestId'");
if(mysqli_num_rows($query) == 0){
    echo json_encode(['status' => 'error', 'message' => 'Request not found']);
    exit;
}
$request = mysqli_fetch_assoc($query);

$personalEmail = $request['personal_email'];
$fullName = $request['full_name'];

// تحديث حالة الطلب بالرفض
if(mysqli_query($conn, "UPDATE instructor_requests SET status='rejected' WHERE id='$requestId'")){

    // إرسال البريد بالرفض
    $mail = new PHPMailer(true);
    try {
        // إعدادات السيرفر
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // ضع هنا SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'generalmoharam5@gmail.com'; // البريد الرسمي لإرسال الرسائل
        $mail->Password = 'cdls qvsm gsxk juee';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('generalmoharam5@gmail.com', 'SmartLearn LMS');
        $mail->addAddress($personalEmail, $fullName);

        $mail->isHTML(true);
        $mail->Subject = 'Instructor Request Status';
        $mail->Body = "Hello $fullName,<br><br>
                       We regret to inform you that your request to become an instructor has been rejected.<br>
                       You can contact the administration if you have any questions.<br><br>
                       Regards,<br>SmartLearn LMS";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Instructor rejected and email sent']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Instructor rejected but email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to reject instructor']);
}
?>
