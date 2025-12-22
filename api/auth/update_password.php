<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

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
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if (!$email || !$oldPassword || !$newPassword) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

// =========================
// دالة للبحث عن المستخدم في أي جدول والتحقق من الباسورد القديم
// =========================
function findUserTable($conn, $email) {
    $tables = ['students', 'instructors', 'admins'];
    foreach ($tables as $table) {
        $email_safe = mysqli_real_escape_string($conn, $email);
        $query = "SELECT * FROM $table WHERE university_email='$email_safe'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            return ['table' => $table, 'user' => $user];
        }
    }
    return false;
}

// =========================
// البحث عن المستخدم
// =========================
$found = findUserTable($conn, $email);
if (!$found) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$table = $found['table'];
$user = $found['user'];

// =========================
// التحقق من الباسورد القديم
// =========================
if (!password_verify($oldPassword, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Old password is incorrect']);
    exit;
}

// =========================
// تحديث الباسورد الجديد
// =========================
$newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
$updateQuery = "UPDATE $table SET password='$newHashed' WHERE university_email='$email'";
if (mysqli_query($conn, $updateQuery)) {
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
}
?>
