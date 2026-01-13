<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    require_once 'config.php';
    echo "✅ Config.php loaded successfully<br>";

    $conn = getConnection();
    echo "✅ Database connection successful<br>";

    // Check if tables exist
    $tables = ['users', 'shopping_lists', 'shopping_items', 'shopping_history'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does NOT exist<br>";
        }
    }

    // Test a simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM shopping_lists");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Shopping lists count: " . $row['count'] . "<br>";
    } else {
        echo "❌ Error querying shopping_lists: " . $conn->error . "<br>";
    }

    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
