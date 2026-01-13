<?php
// Turn off all output buffering
while (ob_get_level()) {
    ob_end_clean();
}

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'success' => false, 
        'message' => 'Not logged in',
        'debug' => ['session_user_id' => null]
    ]));
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

// Get POST data
$list_id = isset($_POST['list_id']) ? intval($_POST['list_id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$category = isset($_POST['category']) ? $_POST['category'] : 'Umum';
$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate
if (empty($title)) {
    die(json_encode(['success' => false, 'message' => 'Judul list harus diisi']));
}

if ($list_id === 0) {
    die(json_encode([
        'success' => false, 
        'message' => 'List ID tidak valid',
        'debug' => ['list_id' => $list_id, 'post_data' => $_POST]
    ]));
}

include("../config.php");
$conn = getConnection();

// Verify ownership
$user_id = intval($_SESSION['user_id']);

// DEBUG: Tampilkan dalam response
$debug_info = [
    'user_id' => $user_id,
    'list_id' => $list_id,
    'session_data' => $_SESSION
];

// Cek dulu apakah list_id ada di database
$check_stmt = $conn->prepare("SELECT id, user_id, title FROM shopping_lists WHERE id = ?");
$check_stmt->bind_param("i", $list_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    die(json_encode([
        'success' => false,
        'message' => 'List ID tidak ditemukan di database',
        'debug' => $debug_info
    ]));
}

$list_data = $check_result->fetch_assoc();
$check_stmt->close();

// Cek kepemilikan
if ($list_data['user_id'] != $user_id) {
    $conn->close();
    die(json_encode([
        'success' => false, 
        'message' => 'Anda tidak memiliki akses ke list ini',
        'debug' => [
            'db_user_id' => $list_data['user_id'],
            'session_user_id' => $user_id,
            'list_title' => $list_data['title']
        ]
    ]));
}

// Update list
$stmt = $conn->prepare("UPDATE shopping_lists SET title = ?, shopping_date = ?, category = ?, budget = ?, notes = ? WHERE id = ?");
if (!$stmt) {
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Prepare update failed: ' . $conn->error]));
}

$stmt->bind_param("sssdsi", $title, $date, $category, $budget, $notes, $list_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true, 
        'message' => 'Berhasil mengupdate list',
        'debug' => ['updated_fields' => compact('title', 'date', 'category', 'budget', 'notes')]
    ]);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    die(json_encode(['success' => false, 'message' => 'Gagal mengupdate: ' . $error]));
}
?>