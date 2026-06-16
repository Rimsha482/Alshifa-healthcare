<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Railway/XAMPP dynamic database connection layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// --- SYSTEM OVERRIDE: DELETE / SUSPEND PATIENT ACCOUNT ---
$message = "";
if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    
    // Safety check constraint: Taakay sirf patient account hi delete ho sake, admin ya doctor nahi
    $delete_query = "DELETE FROM users WHERE id = $remove_id AND (role = 'patient' OR role = 'Patient')";
    if (mysqli_query($conn, $delete_query)) {
        $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong>Success!</strong> Patient account partition successfully removed from directory log.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        $message = "<div class='alert alert-danger'>Execution Fault: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch all system users whose role is patient
$patients = mysqli_query($conn, "SELECT * FROM users WHERE role = 'patient' OR role = 'Patient' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Directory | Control Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; color: #333; }
        .sidebar { background: #1e293b; min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .directory-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3 text-white d-none d-md-block">
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
                <h2 class="fw-bold m-0">Patient Registry Stack</h2>
                <p class="text-muted small">Monitor active consumers, trace system registrations, and manage clinical logs.</p>
            </div>

            <?php echo $message; ?>

            <div class="card directory-card p-4 bg-white">
                <div class="table-responsive">
                    <table class="table align-middle m-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Patient ID</th>
                                <th>Full Name</th>
                                <th>Email Identity</th>
                                <th>Registered Phone No</th>
                                <th>Account Status</th>
                                <th class="text-center">System Override Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($patients) > 0) {
                                while($row = mysqli_fetch_assoc($patients)) {
                            ?>
                            <tr>
                                <td><strong>#PAT-0<?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-person-circle me-2 text-secondary"></i> 
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </div>
                                </td>
                                <td><code class="text-dark"><?php echo htmlspecialchars($row['email']); ?></code></td>
                                <td>
                                    <?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : '<span class="text-muted small">No Phone Set</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-shield-fill-check me-1"></i> Verified Active
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="patient-directory.php?remove_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger px-3 rounded-2"
                                       onclick="return confirm('Warning! Are you completely sure you want to drop and suspend this patient record from active clinic infrastructure registries?');">
                                        <i class="bi bi-person-x-fill me-1"></i> Suspend Account
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                } // Loop end bracket close safely
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No consumers logged inside database active indexes.</td></tr>";
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