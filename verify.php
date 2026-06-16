<?php
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

$msg = "";

if(isset($_POST['verify'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $otp = mysqli_real_escape_string($conn, $_POST['otp']);

    $q = mysqli_query($conn, "SELECT * FROM otp_verification WHERE email='$email' AND otp='$otp'");

    if(mysqli_num_rows($q) > 0){
        mysqli_query($conn, "UPDATE users SET is_verified=1 WHERE email='$email'");
        $msg = "Account Verified!";
    }else{
        $msg = "Invalid OTP!";
    }
}
?>

<h2>Verify OTP</h2>
<?php echo $msg; ?>

<form method="POST">
    <input name="email" placeholder="Email"><br>
    <input name="otp" placeholder="OTP"><br>
    <button name="verify">Verify</button>
</form>