<?php
$host = "localhost";
$db = "smartlearn_lms";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['status'=>'error','message'=>'Database connection failed']));
}
?>
