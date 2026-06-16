<?php
session_start();

// Railway dynamic production credentials connection layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['id'])) {
    $app_id = intval($_GET['id']);
    // Update query
    mysqli_query($conn, "UPDATE appointments SET link_joined = 1 WHERE id = $app_id");
    echo "Success";
}
?>