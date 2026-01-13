<?php
include("config.php");
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$history_id = intval($data['history_id'] ?? 0);

$conn = getConnection();

$stmt = $conn->prepare("SELECT id FROM shopping_history WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $history_id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Riwayat tidak ditemukan']);
    exit();
}
$stmt->close();

$stmt = $conn->prepare("DELETE FROM shopping_history WHERE id = ?");
$stmt->bind_param("i", $history_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus riwayat']);
}

$stmt->close();
$conn->close();
?>