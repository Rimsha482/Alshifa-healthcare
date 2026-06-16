<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection Layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

$message = "";

// --- ACTION: REMOVE PATIENT ACCOUNT ---
if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    
    // Safety check: Sirf patient role ka user delete ho
    $delete_query = "DELETE FROM users WHERE id = $remove_id AND (role = 'patient' OR role = 'Patient')";
    if (mysqli_query($conn, $delete_query)) {
        $message = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        Patient account successfully purged from system directory registry.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch All Patients
$patients_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'patient' OR role = 'Patient' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Directory | Admin Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; }
        .sidebar { background: #1e293b; min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .directory-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center mb-4 px-2">
                <i class="bi bi-heart-pulse-fill text-danger fs-3 me-2"></i>
                <h5 class="m-0 fw-bold">Al Shifa Admin</h5>
            </div>
            <hr class="text-secondary">
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
            <div class="mb-4">
                <h2 class="fw-bold m-0">Patient Registry Core</h2>
                <p class="text-muted small">Monitor all registered service consumers and regulate access permissions.</p>
            </div>

            <?php echo $message; ?>

            <div class="card directory-card p-3 bg-white">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Registered Patients Database</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient UID</th>
                                <th>Full Legal Name</th>
                                <th>Email Identity</th>
                                <th>Contact Number</th>
                                <th class="text-center">Account Security</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($patients_result) > 0) {
                                while($patient = mysqli_fetch_assoc($patients_result)):
                            ?>
                            <tr>
                                <td><strong>#PAT-<?php echo $patient['id']; ?></strong></td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bi bi-person-circle me-1 text-secondary"></i> <?php echo htmlspecialchars($patient['name']); ?></div>
                                </td>
                                <td><code class="text-dark"><?php echo htmlspecialchars($patient['email']); ?></code></td>
                                <td><span class="text-muted fw-medium"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></span></td>
                                <td class="text-center">
                                    <a href="patient-directory.php?remove_id=<?php echo $patient['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger px-3 rounded-2"
                                       onclick="return confirm('Are you completely sure you want to suspend and delete this patient record from active clinic directories?');">
                                        <i class="bi bi-person-x-fill"></i> Suspend Account
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No patients currently registered in the cloud infrastructure database.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>