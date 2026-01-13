<?php
include("../config.php");
requireLogin();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$item_id = intval($data['item_id'] ?? 0);

$conn = getConnection();

// Get current status
$stmt = $conn->prepare("SELECT si.is_checked FROM shopping_items si 
                        JOIN shopping_lists sl ON si.list_id = sl.id 
                        WHERE si.id = ? AND sl.user_id = ?");
$stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    $stmt->close();
    $conn->close();
    exit();
}

$item = $result->fetch_assoc();
$new_status = !$item['is_checked'];
$stmt->close();

// Update status
$stmt = $conn->prepare("UPDATE shopping_items SET is_checked = ? WHERE id = ?");
$stmt->bind_param("ii", $new_status, $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'is_checked' => $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>