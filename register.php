<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";

if (isset($_POST['register_now'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // --- VALIDATION: Only Alphabets and Spaces allowed ---
    if (!preg_match("/^[a-zA-Z ]*$/", $full_name)) {
        $message = "<div class='alert alert-warning shadow-sm'><i class='bi bi-exclamation-triangle'></i> <strong>Error:</strong> Name mein sirf alphabets (A-Z) allowed hain. Numbers ya symbols nahi.</div>";
    } 
    // Check karein ke naam khali to nahi (sirf spaces na hon)
    elseif (empty($full_name)) {
        $message = "<div class='alert alert-warning shadow-sm'>Meharbani karke apna mukammal naam darj karein.</div>";
    }
    else {
        // Check karein ke email pehle se to nahi hai
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "<div class='alert alert-danger shadow-sm'>Yeh email pehle se register hai.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (name, email, password, role) VALUES ('$full_name', '$email', '$hashed_password', '$role')";

            if (mysqli_query($conn, $insert_query)) {
                $message = "<div class='alert alert-success shadow-sm animate__animated animate__fadeIn'><strong>Success!</strong> Account created successfully.</div>";
                header("refresh:2;url=login.php");
            } else {
                $message = "<div class='alert alert-danger shadow-sm'>Database Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Al Shifa HealthCare</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body {
      background: linear-gradient(135deg, #4042a1, #6a67ce);
      min-height: 100vh;
      display: flex;
      align-items: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .register-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      padding: 40px;
      width: 100%;
      max-width: 450px;
    }
    .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1px solid #e0e0e0; }
    .form-control:focus { box-shadow: 0 0 0 3px rgba(64, 66, 161, 0.1); border-color: #4042a1; }
    .btn-register {
      background: #4042a1;
      color: white;
      border-radius: 12px;
      padding: 12px;
      font-weight: 600;
      border: none;
      transition: 0.3s;
    }
    .btn-register:hover { background: #5254c0; transform: translateY(-2px); }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center animate__animated animate__fadeInDown">
  <div class="register-card">
    <div class="text-center mb-4">
        <i class="bi bi-person-plus-fill" style="font-size: 3rem; color: #4042a1;"></i>
        <h3 class="mt-2">Create Account</h3>
        <p class="text-muted small">Join Al Shifa Medical Network</p>
    </div>
    
    <div id="registerMessage"><?php echo $message; ?></div>

    <form action="register.php" method="POST">
      <div class="mb-3">
        <label class="form-label fw-bold small">Full Name (Alphabets Only)</label>
        <input type="text" name="full_name" class="form-control" 
               placeholder="e.g. Rimsha Razzaq" 
               pattern="[A-Za-z ]+" 
               title="Sirf letters aur spaces allow hain (e.g. 123 allowed nahi)" 
               required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold small">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-bold small">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" minlength="6" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label fw-bold small">Register As</label>
        <select class="form-select" name="role" required>
          <option value="patient">Patient</option>
          <option value="doctor">Doctor</option>
        </select>
      </div>
      
      <button type="submit" name="register_now" class="btn btn-register w-100 mt-2">Create Account</button>
    </form>

    <div class="text-center mt-4">
      <span class="small text-muted">Already have an account?</span>
      <a href="login.php" class="text-decoration-none fw-bold small" style="color: #4042a1;"> Login Here</a>
    </div>
  </div>
</div>

</body>
</html>