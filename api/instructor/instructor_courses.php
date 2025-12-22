<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *"); // للسماح بالوصول من أي مكان

include "../db.php"; // الاتصال بقاعدة البيانات

// استلام instructor_id من GET وتحويله لرقم صحيح
$instructor_id = intval($_GET['instructor_id'] ?? 0);

if ($instructor_id === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Instructor ID is required'
    ]);
    exit;
}

// جلب الكورسات مباشرة من جدول courses حسب instructor_id
$stmt = $conn->prepare("
    SELECT id, title, description
    FROM courses
    WHERE instructor_id = ?
");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// إرجاع JSON متوافق مع Flutter
echo json_encode([
    'status' => 'success',
    'courses' => $courses
]);
?>
