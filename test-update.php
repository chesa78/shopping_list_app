<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Update List</h2>";

// Simulate POST data
$_POST = [
    'list_id' => 1,
    'title' => 'Test List Updated',
    'date' => '2024-01-15',
    'category' => 'Makanan',
    'budget' => 50000,
    'notes' => 'Test notes'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate session
session_start();
$_SESSION['user_id'] = 1;

echo "<h3>Simulated POST Data:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>Simulated Session:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Include the update script
echo "<h3>Running update-list.php:</h3>";
include('php/update-list.php');
?>
