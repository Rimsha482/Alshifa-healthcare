<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

if (!$conn) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

$error = "";
$login_success = false; 
$redirect_url = "index.php"; // Default fallback

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
       // Is block ko login.php mein badal dein
if (password_verify($password, $user['password'])) {
    // Agar aap status check nahi karna chahte, toh direct session banayein
    $_SESSION['user_id'] = $user['id']; 
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    
    $role = strtolower(trim($user['role']));
    $_SESSION['user_role'] = $role;
    $_SESSION['role'] = $role;
    
    $login_success = true; 

    if ($role == 'admin') {
        $redirect_url = "admin-dashboard.php";
    } elseif ($role == 'doctor') {
        $redirect_url = "doctor-dashboard.php";
    } else {
        $redirect_url = "patient-dashboard.php";
    }
} else {
    $error = "Wrong Password! Check it again.";
}
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Al Shifa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f4f7fe; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); display: flex; max-width: 900px; width: 100%; border: none; overflow: hidden; }
        .sidebar { background: #5c61d5; color: white; padding: 50px; width: 45%; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .form-area { padding: 50px; width: 55%; }
        .btn-login { background: #5c61d5; color: white; border: none; padding: 14px; border-radius: 12px; width: 100%; font-weight: 700; transition: 0.3s ease; }
        .btn-login:hover { background: #4a4fa8; }
        .form-control { border-radius: 10px; padding: 12px; }
        .register-link a { color: #5c61d5; text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }
        
        @media (max-width: 767.98px) {
            .sidebar { display: none !important; }
            .form-area { width: 100%; padding: 30px 20px; }
        }
    </style>
</head>
<body>

<?php if($login_success): ?>
<script>
    Swal.fire({
        title: 'Welcome Back!',
        text: 'Login Successful. Redirecting...',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false
    }).then(() => {
        // Dashboard dynamic URL par safely bhejega
        window.location.href = '<?php echo $redirect_url; ?>'; 
    });
</script>
<?php endif; ?>

<div class="login-card">
    <div class="sidebar d-none d-md-flex">
        <i class="bi bi-heart-pulse-fill mb-3" style="font-size: 4rem;"></i>
        <h1 class="fw-bold">Al Shifa</h1>
        <p class="opacity-75">Healthcare Management</p>
    </div>
    <div class="form-area">
        <h2 class="fw-bold mb-4">Sign In</h2>
        
        <?php if($error != ""): ?>
            <div class="alert alert-danger py-2 px-3 small border-0 mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn-login mb-4">Sign In</button>
            
            <div class="text-center register-link small text-muted">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>