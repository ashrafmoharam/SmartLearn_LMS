<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

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
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

// =========================
// التحقق من الأدمن
// =========================
$email_safe = mysqli_real_escape_string($conn, $email);
$query = "SELECT * FROM admins WHERE university_email='$email_safe' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}

$admin = mysqli_fetch_assoc($result);

if (!password_verify($password, $admin['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}

// =========================
// نجاح تسجيل الدخول
// =========================
echo json_encode([
    'status' => 'success',
    'role' => 'admin',
    'id' => $admin['id'],
    'name' => $admin['full_name'],
]);
exit;
?>
