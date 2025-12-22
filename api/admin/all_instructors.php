<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "../db.php";

// استعلام لجلب كل المعلمين
$result = mysqli_query($conn, "SELECT id, full_name, personal_email, university_email FROM instructors");

$instructors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $instructors[] = $row;
}

// نرجع النتيجة بصيغة JSON
echo json_encode([
    "status" => "success",
    "instructors" => $instructors
]);
?>
