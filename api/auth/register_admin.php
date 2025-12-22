<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

// =========================
// اتصال قاعدة البيانات
// =========================
$host = "localhost";
$db   = "smartlearn_lms";
$user = "root";
$pass = "";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to database']);
    exit;
}

// =========================
// استلام البيانات من الطلب
// =========================
$full_name = $_POST['full_name'] ?? '';
$personal_email = $_POST['personal_email'] ?? '';
$university_email = $_POST['university_email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$full_name || !$personal_email || !$university_email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// =========================
// التحقق من البريد الجامعي أو الشخصي إذا موجود مسبقًا
// =========================
$email_check_query = "SELECT id FROM admins WHERE personal_email='$personal_email' OR university_email='$university_email'";
$email_check_result = mysqli_query($conn, $email_check_query);

if (mysqli_num_rows($email_check_result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

// =========================
// تشفير الباسورد
// =========================
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// =========================
// إدخال البيانات في جدول admins
// =========================
$insert_query = "INSERT INTO admins (full_name, personal_email, university_email, password) 
                 VALUES ('$full_name', '$personal_email', '$university_email', '$hashed_password')";

if (mysqli_query($conn, $insert_query)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin registered successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to register admin: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
