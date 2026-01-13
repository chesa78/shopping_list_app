<?php
include("../config.php");
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$item_id = intval($_POST['item_id'] ?? 0);
$item_name = trim($_POST['item_name'] ?? '');
$quantity = trim($_POST['quantity'] ?? '1');
$price = floatval($_POST['price'] ?? 0);
$category = $_POST['category'] ?? 'Lainnya';
$priority = $_POST['priority'] ?? 'sedang';
$notes = trim($_POST['notes'] ?? '');

if (empty($item_name)) {
    echo json_encode(['success' => false, 'message' => 'Nama barang harus diisi']);
    exit();
}

$conn = getConnection();

$stmt = $conn->prepare("SELECT si.id FROM shopping_items si 
                        JOIN shopping_lists sl ON si.list_id = sl.id 
                        WHERE si.id = ? AND sl.user_id = ?");
$stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    exit();
}
$stmt->close();

$stmt = $conn->prepare("UPDATE shopping_items SET item_name = ?, quantity = ?, price = ?, category = ?, priority = ?, notes = ? WHERE id = ?");
$stmt->bind_param("ssdsssi", $item_name, $quantity, $price, $category, $priority, $notes, $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate item']);
}

$stmt->close();
$conn->close();
?>