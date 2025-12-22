<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// الاتصال بقاعدة البيانات
include_once __DIR__ . "/../db.php";

$assignment_id = $_GET['assignment_id'] ?? '';

if (empty($assignment_id)) {
    echo json_encode([
        "status" => "error",
        "message" => "Assignment ID is required"
    ]);
    exit;
}

// استعلام لجلب جميع submissions مع اسم الطالب و البريد الجامعي و due_date
$stmt = $conn->prepare(
    "SELECT s.id, s.assignment_id, s.student_id, s.submission_file, s.submitted_at,
            st.full_name AS student_name,
            st.university_email,
            a.due_date
     FROM assignment_submissions s
     JOIN students st ON s.student_id = st.id
     JOIN assignments a ON s.assignment_id = a.id
     WHERE s.assignment_id = ?
     ORDER BY s.submitted_at DESC"
);

$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$result = $stmt->get_result();

$submissions = [];
while ($row = $result->fetch_assoc()) {
    $submissions[] = $row;
}

echo json_encode([
    "status" => "success",
    "submissions" => $submissions
]);
