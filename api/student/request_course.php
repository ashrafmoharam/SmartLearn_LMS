<?php
// إخفاء الأخطاء للتشغيل الطبيعي
ini_set('display_errors', 0);
error_reporting(0);

// ضبط الهيدر لتكون JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

// معلومات الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "smartlearn_lms";

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// استلام البيانات من POST
$student_id = $_POST['student_id'] ?? '';
$course_id = $_POST['course_id'] ?? '';

if (!$student_id || !$course_id) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// التحقق من وجود الطالب
$check_student = $conn->prepare("SELECT id FROM students WHERE id = ?");
$check_student->bind_param("i", $student_id);
$check_student->execute();
$result_student = $check_student->get_result();
if ($result_student->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student not found"]);
    exit;
}

// التحقق من وجود المادة
$check_course = $conn->prepare("SELECT id FROM courses WHERE id = ?");
$check_course->bind_param("i", $course_id);
$check_course->execute();
$result_course = $check_course->get_result();
if ($result_course->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Course not found"]);
    exit;
}

// التحقق إذا سبق إرسال نفس الطلب
$check_request = $conn->prepare("SELECT id FROM course_requests WHERE student_id=? AND course_id=?");
$check_request->bind_param("ii", $student_id, $course_id);
$check_request->execute();
$result_check = $check_request->get_result();
if ($result_check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "You have already requested this course"]);
    exit;
}

// إدخال الطلب
$insert_request = $conn->prepare("INSERT INTO course_requests (student_id, course_id, created_at) VALUES (?, ?, NOW())");
$insert_request->bind_param("ii", $student_id, $course_id);

if ($insert_request->execute()) {
    echo json_encode(["status" => "success", "message" => "Request submitted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to submit request"]);
}

// غلق الاتصال
$check_student->close();
$check_course->close();
$check_request->close();
$insert_request->close();
$conn->close();
?>
