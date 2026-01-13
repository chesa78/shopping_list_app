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

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$list_id = isset($data['list_id']) ? intval($data['list_id']) : 0;

if ($list_id === 0) {
    die(json_encode(['success' => false, 'message' => 'List ID tidak valid']));
}

include("../config.php");
$conn = getConnection();

$user_id = intval($_SESSION['user_id']);

// Get list data
$stmt = $conn->prepare("SELECT * FROM shopping_lists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $list_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'List tidak ditemukan']));
}

$list = $result->fetch_assoc();
$stmt->close();

// Get items
$stmt = $conn->prepare("SELECT * FROM shopping_items WHERE list_id = ?");
$stmt->bind_param("i", $list_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'List tidak memiliki barang. Tambahkan barang terlebih dahulu.']));
}

// Calculate totals
$total_items = count($items);
$total_spent = 0;
foreach ($items as $item) {
    $total_spent += floatval($item['price']);
}

// Save to history
$items_json = json_encode($items);
$stmt = $conn->prepare("INSERT INTO shopping_history (user_id, title, shopping_date, total_items, total_spent, budget, category, notes, items_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issiddsss", 
    $user_id,
    $list['title'], 
    $list['shopping_date'], 
    $total_items, 
    $total_spent, 
    $list['budget'], 
    $list['category'], 
    $list['notes'], 
    $items_json
);

if ($stmt->execute()) {
    $stmt->close();
    
    // Delete the original list
    $stmt2 = $conn->prepare("DELETE FROM shopping_lists WHERE id = ?");
    $stmt2->bind_param("i", $list_id);
    $stmt2->execute();
    $stmt2->close();
    
    $conn->close();
    die(json_encode(['success' => true, 'message' => 'List berhasil disimpan ke riwayat']));
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Gagal menyimpan ke riwayat: ' . $error]));
}
?>