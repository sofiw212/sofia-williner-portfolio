<?php
// config/db.php - LOCAL XAMPP VERSION
$host = 'localhost';
$username = 'root';
$password = '';          // default XAMPP root password is empty
$database = 'enda_platform';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>