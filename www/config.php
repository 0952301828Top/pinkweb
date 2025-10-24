<?php
$servername = "db";    // ต้องเป็นชื่อ service ของ MySQL
$username = "root";    // ตาม docker-compose
$password = "root";    // ตาม docker-compose
$dbname   = "www";    // ตาม docker-compose

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
