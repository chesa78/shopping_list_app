<?php
include("config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesi login habis'
    ]);
    exit;
}

$title    = trim($_POST['title'] ?? '');
$dateRaw  = $_POST['date'] ?? '';
$category = $_POST['category'] ?? 'Umum';
$budget   = (float) ($_POST['budget'] ?? 0);
$notes    = trim($_POST['notes'] ?? '');

if ($title === '') {
    echo json_encode(['success' => false, 'message' => 'Judul wajib diisi']);
    exit;
}

$date = date('Y-m-d', strtotime($dateRaw));

$conn = getConnection();

$sql = "INSERT INTO shopping_lists
(user_id, title, shopping_date, category, budget, notes)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssds",
    $_SESSION['user_id'],
    $title,
    $date,
    $category,
    $budget,
    $notes
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();



