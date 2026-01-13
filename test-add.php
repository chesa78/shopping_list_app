<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing add-item.php...<br><br>";

$_POST['list_id'] = 1;
$_POST['item_name'] = 'Test Item';
$_POST['quantity'] = '1';
$_POST['price'] = 10000;
$_POST['category'] = 'Lainnya';
$_POST['priority'] = 'sedang';
$_POST['notes'] = '';
$_SERVER['REQUEST_METHOD'] = 'POST';

session_start();
$_SESSION['user_id'] = 1; // Ganti dengan user ID kamu

include 'php/add-item.php';
?>