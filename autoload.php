<?php
// Railway ke database credentials ko PHP ke local variables mein force karna
if (isset($_ENV['MYSQLHOST'])) {
    $GLOBALS['host'] = $_ENV['MYSQLHOST'];
    $GLOBALS['user'] = $_ENV['MYSQLUSER'];
    $GLOBALS['password'] = $_ENV['MYSQLPASSWORD'];
    $GLOBALS['database'] = $_ENV['MYSQLDATABASE'];
    $GLOBALS['port'] = $_ENV['MYSQLPORT'];
    
    // Agar aapki files mein ye standard variable names use huay hain:
    $servername = $_ENV['MYSQLHOST'];
    $username = $_ENV['MYSQLUSER'];
    $db_password = $_ENV['MYSQLPASSWORD'];
    $dbname = $_ENV['MYSQLDATABASE'];
}
?>