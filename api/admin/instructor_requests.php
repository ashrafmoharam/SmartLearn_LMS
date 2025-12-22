<?php
header("Content-Type: application/json; charset=UTF-8");
include "../db.php";

$query = "SELECT id, full_name, personal_email, phone, field, status, cv_file, created_at 
          FROM instructor_requests 
          WHERE status='pending'";
$result = mysqli_query($conn, $query);

$requests = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "requests" => $requests
]);
?>
