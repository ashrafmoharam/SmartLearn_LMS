<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");

include '../db.php';

$instructor_id = $_GET['instructor_id'] ?? '';

if ($instructor_id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Instructor ID is required'
    ]);
    exit;
}

// جلب الأخبار الخاصة بالمدرس
$stmt = $conn->prepare("
    SELECT 
        id,
        title,
        content,
        created_at
    FROM news
    WHERE instructor_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$news = [];
while ($row = $result->fetch_assoc()) {
    $news[] = $row;
}

echo json_encode([
    'status' => 'success',
    'news' => $news
]);
?>
