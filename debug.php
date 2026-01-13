<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Shopping List App</h2>";

echo "<h3>1. Testing PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>2. Testing Database Connection</h3>";
try {
    require_once 'php/config.php';
    echo "✅ Config.php loaded successfully<br>";
    
    $conn = getConnection();
    echo "✅ Database connection successful<br>";
    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Testing Session</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not logged in') . "<br>";

echo "<h3>4. Testing Add Item (Simulated)</h3>";
$_POST['list_id'] = 1;
$_POST['item_name'] = 'Test Item Debug';
$_POST['quantity'] = '1 kg';
$_POST['price'] = 5000;
$_POST['category'] = 'Makanan';
$_POST['priority'] = 'sedang';
$_POST['notes'] = 'Testing';
$_SERVER['REQUEST_METHOD'] = 'POST';

if (isset($_SESSION['user_id'])) {
    echo "Attempting to add item...<br>";
    
    $list_id = intval($_POST['list_id']);
    $item_name = trim($_POST['item_name']);
    $quantity = trim($_POST['quantity']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $notes = trim($_POST['notes']);
    
    $conn = getConnection();
    
    // Check if list exists
    $stmt = $conn->prepare("SELECT id FROM shopping_lists WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $list_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ List found<br>";
        
        $stmt2 = $conn->prepare("INSERT INTO shopping_items (list_id, item_name, quantity, price, category, priority, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("issdsss", $list_id, $item_name, $quantity, $price, $category, $priority, $notes);
        
        if ($stmt2->execute()) {
            echo "✅ Item added successfully! ID: " . $stmt2->insert_id . "<br>";
        } else {
            echo "❌ Failed to add item: " . $stmt2->error . "<br>";
        }
        $stmt2->close();
    } else {
        echo "❌ List not found or not owned by current user<br>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "❌ User not logged in<br>";
}

echo "<h3>5. Check Files</h3>";
$files = [
    'php/config.php',
    'php/add-item.php',
    'php/toggle-item.php',
    'php/update-item.php',
    'php/delete-item.php',
    'php/update-list.php',
    'php/complete-list.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file NOT FOUND<br>";
    }
}
?>