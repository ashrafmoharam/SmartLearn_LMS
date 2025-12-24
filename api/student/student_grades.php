<?php
header("Content-Type: application/json; charset=UTF-8");
include "../db.php";

$student_id = $_GET['student_id'] ?? '';

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID required']);
    exit;
}

// جلب جميع الاختبارات التي أجيب عنها الطالب
$quizQuery = "
    SELECT DISTINCT quiz_id
    FROM quiz_answers
    WHERE student_id = ?
";
$stmt = $conn->prepare($quizQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row['quiz_id'];
}

$grades = [];

foreach ($quizzes as $quiz_id) {
    // جلب الأسئلة الصحيحة
    $correctQuery = "SELECT id AS question_id, correct_answer FROM quiz_questions WHERE quiz_id = ?";
    $stmtC = $conn->prepare($correctQuery);
    $stmtC->bind_param("i", $quiz_id);
    $stmtC->execute();
    $resC = $stmtC->get_result();

    $correctAnswers = [];
    $totalMarks = 0;
    while ($rowC = $resC->fetch_assoc()) {
        $correctAnswers[$rowC['question_id']] = $rowC['correct_answer'];
        $totalMarks += 1; // كل سؤال = 1 نقطة، يمكن تعديل حسب الوزن
    }

    // جلب إجابات الطالب
    $answersQuery = "SELECT question_id, answer, submitted_at FROM quiz_answers WHERE student_id = ? AND quiz_id = ?";
    $stmtA = $conn->prepare($answersQuery);
    $stmtA->bind_param("ii", $student_id, $quiz_id);
    $stmtA->execute();
    $resA = $stmtA->get_result();

    $score = 0;
    $submittedAt = null;

    while ($rowA = $resA->fetch_assoc()) {
        $submittedAt = $rowA['submitted_at']; // آخر تاريخ للإجابة
        $qId = $rowA['question_id'];
        $answer = $rowA['answer'];

        if (isset($correctAnswers[$qId]) && $correctAnswers[$qId] == $answer) {
            $score += 1;
        }
    }

    // جلب عنوان الاختبار
    $quizTitleQuery = "SELECT title FROM quizzes WHERE id = ?";
    $stmtQ = $conn->prepare($quizTitleQuery);
    $stmtQ->bind_param("i", $quiz_id);
    $stmtQ->execute();
    $resQ = $stmtQ->get_result();
    $quizTitle = $resQ->fetch_assoc()['title'] ?? 'Unknown Quiz';

    $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0;

    $grades[] = [
        'quiz_id' => $quiz_id,
        'quiz_title' => $quizTitle,
        'score' => $score,
        'total_marks' => $totalMarks,
        'percentage' => $percentage,
        'submitted_at' => $submittedAt,
    ];
}

echo json_encode([
    'status' => 'success',
    'grades' => $grades
]);
?>
