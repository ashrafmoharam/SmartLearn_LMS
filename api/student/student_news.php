<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// الاتصال بقاعدة البيانات
include_once __DIR__ . "/../db.php";

// استعلام لجلب كل الأخبار مع اسم المعلم
$query = "
    SELECT n.id, n.title, n.content, n.created_at, i.full_name AS instructor_name
    FROM news n
    LEFT JOIN instructors i ON n.instructor_id = i.id
    ORDER BY n.created_at DESC
";

$result = $conn->query($query);

$news = [];
while($row = $result->fetch_assoc()) {
    $news[] = $row;
}

echo json_encode([
    "status" => "success",
    "news" => $news
]);
