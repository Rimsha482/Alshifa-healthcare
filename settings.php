<?php
session_start();

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

// Check agar user login nahi hai to login page par bhej dein
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 🔹 POST Request: Data Update Karna
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    $update_sql = "UPDATE users SET name='$name', phone='$phone', bio='$bio' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['user_name'] = $name; // Session update karein taake navbar par naam badal jaye
        $message = "<div class='alert alert-success small'>Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger small'>Error updating profile.</div>";
    }
}

// 🔹 GET Request: Mojooda Data Load Karna
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user_data = mysqli_fetch_assoc($result);

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings - Al Shifa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --primary: #4042a1; --bg: #f4f7fe; }
        body { background-color: var(--bg); font-family: 'Segoe UI', sans-serif; }
        .settings-card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .profile-header {
            background: linear-gradient(135deg, var(--primary), #6a67ce);
            height: 120px; position: relative;
        }
        .profile-img-container {
            position: absolute; bottom: -40px; left: 30px;
        }
        .profile-img {
            width: 100px; height: 100px;
            border: 5px solid white; border-radius: 50%;
            background: white; object-fit: cover;
        }
        .form-label { font-weight: 600; color: #555; font-size: 0.9rem; }
        .btn-save { background: var(--primary); color: white; border-radius: 8px; font-weight: 600; padding: 10px 25px; border: none; }
        .btn-save:hover { background: #33358a; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <a href="index.php" class="btn btn-link text-decoration-none text-muted mb-3 p-0">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>

            <div class="card settings-card">
                <div class="profile-header">
                    <div class="profile-img-container">
                        <!-- Dynamic Avatar based on Name -->
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['name']); ?>&background=4042a1&color=fff" class="profile-img">
                    </div>
                </div>
                
                <div class="card-body mt-5 pt-4">
                    <h4 class="fw-bold mb-1">Edit Profile</h4>
                    <p class="text-muted small mb-4">Update your personal information at Al Shifa HealthCare.</p>

                    <?php echo $message; ?>

                    <form action="settings.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+92 3xx xxxxxxx" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (Read Only)</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Bio / Professional Summary</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <a href="settings.php?logout=1" class="btn btn-light text-danger fw-bold">Logout</a>
                            <button type="submit" class="btn btn-save">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>