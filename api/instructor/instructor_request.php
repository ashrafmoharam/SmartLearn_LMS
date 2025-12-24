<?php
// =======================
// NO HTML ERRORS
// =======================
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

include "../db.php";

// =======================
// VALIDATION
// =======================
if (
    empty($_FILES['cv_file']['name']) ||
    empty($_FILES['cv_file']['tmp_name']) ||
    empty($_POST['full_name']) ||
    empty($_POST['personal_email']) ||
    empty($_POST['phone'])
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required data'
    ]);
    exit;
}

$cv_file        = $_FILES['cv_file'];
$full_name      = trim($_POST['full_name']);
$personal_email = trim($_POST['personal_email']);
$phone          = trim($_POST['phone']);

// =======================
// FILE CHECK
// =======================
if ($cv_file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'error',
        'message' => 'File upload error'
    ]);
    exit;
}

// =======================
// SAVE FILE
// =======================
$target_dir = "../../uploads/cvs/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$fileName = time() . "_" . basename($cv_file["name"]);
$target_file = $target_dir . $fileName;

if (!move_uploaded_file($cv_file["tmp_name"], $target_file)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save CV'
    ]);
    exit;
}

// =======================
// INSERT REQUEST
// =======================
$stmt = $conn->prepare(
    "INSERT INTO instructor_requests
     (full_name, personal_email, phone, cv_file)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed'
    ]);
    exit;
}

$stmt->bind_param("ssss", $full_name, $personal_email, $phone, $fileName);

if (!$stmt->execute()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
    exit;
}

$stmt->close();

// =======================
// AI CHECK
// =======================
$cvText = "Flutter developer with Dart and Firebase experience";

$aiData = json_encode(["cv_text" => $cvText]);

$ch = curl_init("http://127.0.0.1:8000/check_cv");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => $aiData,
    CURLOPT_TIMEOUT        => 5,
]);

$aiResponse = curl_exec($ch);
curl_close($ch);

$aiResult = json_decode($aiResponse, true);

// DEFAULT VALUES
$decision = $aiResult['decision'] ?? 'pending';
$score    = $aiResult['score'] ?? 0;
$reason   = $aiResult['reason'] ?? 'AI not available';

// =======================
// SAVE AI RESULT
// =======================
$aiStmt = $conn->prepare(
    "INSERT INTO instructor_ai_results
     (email, decision, score, reason)
     VALUES (?, ?, ?, ?)"
);

if ($aiStmt) {
    $aiStmt->bind_param("ssds", $personal_email, $decision, $score, $reason);
    $aiStmt->execute();
    $aiStmt->close();
}

// =======================
// FINAL RESPONSE
// =======================
echo json_encode([
    'status'   => 'success',
    'decision' => $decision,
    'score'    => $score,
    'reason'   => $reason
]);

$conn->close();
