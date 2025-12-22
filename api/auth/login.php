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
// دالة للتحقق في جدول معين
// =========================
function checkUser($conn, $table, $email, $password) {
    $email_safe = mysqli_real_escape_string($conn, $email);
    $query = "SELECT * FROM $table WHERE university_email='$email_safe' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) return false;

    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// =========================
// البحث في جميع الجداول
// =========================
$tables = ['admins', 'instructors', 'students'];
foreach ($tables as $table) {
    $user = checkUser($conn, $table, $email, $password);
    if ($user) {
        echo json_encode([
            'status' => 'success',
            'role' => rtrim($table, 's'), // admin, instructor, student
            'id' => $user['id'],
            'name' => $user['full_name'],
        ]);
        exit;
    }
}

// =========================
// اذا لم يجد المستخدم
// =========================
echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
exit;
?>
