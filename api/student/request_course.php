<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "smartlearn_lms";

$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["status"=>"error","message"=>"Database connection failed"]);
    exit;
}

// قراءة JSON من body
$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? '';
$course_id = $data['course_id'] ?? '';
$student_email = $data['student_email'] ?? '';

if (!$student_id || !$course_id || !$student_email) {
    echo json_encode(["status"=>"error","message"=>"All fields are required"]);
    exit;
}

// تحقق الطالب
$check_student = $conn->prepare("SELECT id FROM students WHERE id=?");
$check_student->bind_param("i", $student_id);
$check_student->execute();
$result_student = $check_student->get_result();
if ($result_student->num_rows === 0) {
    echo json_encode(["status"=>"error","message"=>"Student not found"]);
    exit;
}

// تحقق المادة
$check_course = $conn->prepare("SELECT id FROM courses WHERE id=?");
$check_course->bind_param("i", $course_id);
$check_course->execute();
$result_course = $check_course->get_result();
if ($result_course->num_rows === 0) {
    echo json_encode(["status"=>"error","message"=>"Course not found"]);
    exit;
}

// تحقق من الطلب السابق
$check_sql = $conn->prepare("SELECT id FROM course_requests WHERE student_id=? AND course_id=?");
$check_sql->bind_param("ii", $student_id, $course_id);
$check_sql->execute();
$result_check = $check_sql->get_result();
if ($result_check->num_rows > 0) {
    echo json_encode(["status"=>"error","message"=>"You have already requested this course"]);
    exit;
}

// إدخال الطلب مع البريد الجامعي
$insert_sql = $conn->prepare("INSERT INTO course_requests (student_id, course_id, student_email, created_at) VALUES (?, ?, ?, NOW())");
$insert_sql->bind_param("iis", $student_id, $course_id, $student_email);

if ($insert_sql->execute()) {
    echo json_encode(["status"=>"success","message"=>"Request submitted successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Failed to submit request"]);
}

$check_student->close();
$check_course->close();
$check_sql->close();
$insert_sql->close();
$conn->close();
?>
