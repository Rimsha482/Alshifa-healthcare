<?php
session_start(); // Pehle session start karna zaroori hai taake usay khatam kiya ja sakay

// 1. Saare session variables ko khali karein
$_SESSION = array();

// 2. Agar session cookie use ho rahi hai to usay expire karein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session ko mukammal khatam (destroy) karein
session_destroy();

// 4. User ko login page par bhej dein
header("Location: index.php");
exit();
?>