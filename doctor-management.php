<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

$message = "";

// --- 1. ADD NEW DOCTOR ENGINE ---
if (isset($_POST['add_doctor'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password_input = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure Password Encryption
    
    // Check if email already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-danger'>Error: A user with this email already exists.</div>";
    } else {
        $insert = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$password_input', 'doctor')";
        if (mysqli_query($conn, $insert)) {
            $message = "<div class='alert alert-success'>Dr. $name registered successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// --- 2. DELETE DOCTOR ENGINE ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    if (mysqli_query($conn, "DELETE FROM users WHERE id = $del_id AND (role='doctor' OR role='Doctor')")) {
        header("Location: doctor-management.php?msg=deleted");
        exit();
    }
}

// Fetch all doctors
$doctors = mysqli_query($conn, "SELECT * FROM users WHERE role = 'doctor' OR role = 'Doctor' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Management | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; }
        .sidebar { background: #1e293b; min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link.active { background: #334155; color: #fff; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3 text-white">
            <h5 class="fw-bold px-2 mb-4"><i class="bi bi-heart-pulse-fill text-danger me-2"></i>Al Shifa</h5>
            <ul class="nav flex-column w-100">
                    <li class="nav-item"><a href="admin-dashboard.php" class="nav-link active"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="doctor-management.php" class="nav-link"><i class="bi bi-person-md me-2"></i> Doctor Management</a></li>
                    <li class="nav-item"><a href="my-appointments.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i> My Appointments</a></li>
                    <li class="nav-item"><a href="patient-directory.php" class="nav-link"><i class="bi bi-people me-2"></i> Patient Directory</a></li>
                    <li class="nav-item"><a href="specialties-settings.php" class="nav-link"><i class="bi bi-sliders me-2"></i> Specialties Settings</a></li>
                    <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-house-door me-2"></i> View Home Page</a></li>
                </ul>
        </div>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <h2 class="fw-bold mb-4">Doctor Management</h2>
            <?php echo $message; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted') echo "<div class='alert alert-warning'>Doctor record removed from system database.</div>"; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card p-4 bg-white">
                        <h5 class="fw-bold mb-3"><i class="bi bi-person-plus me-2 text-primary"></i>Onboard New Consultant</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Alaina" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="name@alshifa.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Phone / JazzCash / EasyPaisa No</label>
                                <input type="text" name="phone" class="form-control" placeholder="e.g. 03001234567" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Portal Access Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="add_doctor" class="btn btn-primary w-100 rounded-pill fw-bold">REGISTER DOCTOR</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card p-3 bg-white">
                        <h5 class="fw-bold mb-3"><i class="bi bi-list-stars me-2 text-success"></i>Active Medical Practitioners</h5>
                        <div class="table-responsive">
                            <table class="table align-middle m-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Doctor Name</th>
                                        <th>Email</th>
                                        <th>Payment Account</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($doctors)): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><span class="badge bg-secondary-subtle text-dark"><?php echo htmlspecialchars($row['phone']); ?></span></td>
                                        <td class="text-center">
                                            <a href="doctor-management.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to offboard this practitioner?');">
                                                <i class="bi bi-trash"></i> Remove
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>