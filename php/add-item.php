<?php
// Turn off all output buffering
while (ob_get_level()) {
    ob_end_clean();
}

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get POST data
$list_id = isset($_POST['list_id']) ? intval($_POST['list_id']) : 0;
$item_name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
$quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '1';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$category = isset($_POST['category']) ? $_POST['category'] : 'Lainnya';
$priority = isset($_POST['priority']) ? $_POST['priority'] : 'sedang';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate
if (empty($item_name)) {
    die(json_encode(['success' => false, 'message' => 'Nama barang harus diisi']));
}

if ($list_id === 0) {
    die(json_encode(['success' => false, 'message' => 'List ID tidak valid']));
}

include("../config.php");
$conn = getConnection();

// Verify list ownership
$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT id FROM shopping_lists WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("ii", $list_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'List tidak ditemukan atau bukan milik Anda']));
}
$stmt->close();

// Insert item
$stmt = $conn->prepare("INSERT INTO shopping_items (list_id, item_name, quantity, price, category, priority, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Prepare insert failed: ' . $conn->error]));
}

$stmt->bind_param("issdsss", $list_id, $item_name, $quantity, $price, $category, $priority, $notes);

if ($stmt->execute()) {
    $item_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => true, 'item_id' => $item_id, 'message' => 'Berhasil menambah item']));
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Gagal menambah item: ' . $error]));
}
?>