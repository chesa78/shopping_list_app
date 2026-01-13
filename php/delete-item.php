<?php
include("../config.php");
requireLogin();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$item_id = intval($data['item_id'] ?? 0);

$conn = getConnection();

// Verify ownership
$stmt = $conn->prepare("SELECT si.id FROM shopping_items si 
                        JOIN shopping_lists sl ON si.list_id = sl.id 
                        WHERE si.id = ? AND sl.user_id = ?");
$stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Delete item
$stmt = $conn->prepare("DELETE FROM shopping_items WHERE id = ?");
$stmt->bind_param("i", $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus item: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>