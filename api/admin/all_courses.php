<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "../db.php"; // تأكد من المسار

if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

$query = "SELECT id, title, description FROM courses";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($conn)
    ]);
    exit;
}

$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

echo json_encode([
    "status" => "success",
    "courses" => $courses
]);

mysqli_close($conn);
?>
