<?php
// Railway live database credentials
$railway_host = $_ENV['MYSQLHOST'] ?? 'localhost';
$railway_user = $_ENV['MYSQLUSER'] ?? 'root';
$railway_pass = $_ENV['MYSQLPASSWORD'] ?? '';
$railway_db   = $_ENV['MYSQLDATABASE'] ?? 'alshifa-db';
$railway_port = $_ENV['MYSQLPORT'] ?? '3306';

// 1. Agar kisi file mein variables ke naam mukhtalif hain, toh unhe force karein
$host = $railway_host;
$user = $railway_user;
$password = $railway_pass;
$database = $railway_db;
$port = $railway_port;

$servername = $railway_host;
$username = $railway_user;
$db_password = $railway_pass;
$dbname = $railway_db;

// 2. MySQLi ke default connection parameters ko live server par redirect karna
mysqli_report(MYSQLI_REPORT_OFF);
ini_set('mysqli.default_host', $railway_host);
ini_set('mysqli.default_user', $railway_user);
ini_set('mysqli.default_pw', $railway_pass);
ini_set('mysqli.default_port', $railway_port);

// 3. Agar kisi file mein direct bina variable ke mysqli_connect() chal raha ho, toh use handle karna
function global_db_connect() {
    global $railway_host, $railway_user, $railway_pass, $railway_db, $railway_port;
    return mysqli_connect($railway_host, $railway_user, $railway_pass, $railway_db, $railway_port);
}
?>