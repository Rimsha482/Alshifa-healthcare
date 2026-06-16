<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Security Check: Agar admin logged in nahi hai ya uska role admin nahi hai, to index.php par bhejo
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'Admin')) {
    header("Location: index.php");
    exit();
}

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

// --- 1. ACTION ENGINE: APPROVE / REJECT SLIPS ---
$action_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['update_status']); // Direct button value capture fix
    
    $update_query = "UPDATE appointments SET fee_status = '$new_status' WHERE id = $appointment_id";
    if (mysqli_query($conn, $update_query)) {
        $action_message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>Success!</strong> Appointment ID #$appointment_id status updated to $new_status.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                           </div>";
    } else {
        $action_message = "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
    }
}

// --- 2. DYNAMIC QUANTITATIVE COUNTERS ---
$doc_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'doctor' OR role = 'Doctor'");
$count_data = mysqli_fetch_assoc($doc_query);
$total_doctors = $count_data['total'] ?? 0;

$pat_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'patient' OR role = 'Patient'");
$count_data = mysqli_fetch_assoc($pat_query);
$total_patients = $count_data['total'] ?? 0;

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE fee_status = 'Pending Verification'");
$count_data = mysqli_fetch_assoc($pending_query);
$pending_slips = $count_data['total'] ?? 0;

$rev_query = mysqli_query($conn, "SELECT SUM(fee_amount) as earnings FROM appointments WHERE fee_status = 'Confirmed' OR fee_status = 'Paid'");
$count_data = mysqli_fetch_assoc($rev_query);
$total_earnings = $count_data['earnings'] ?? 0;

// --- 3. MASTER FETCH DIRECTORY ---
$master_query = "SELECT * FROM appointments ORDER BY id DESC";
$appointments_result = mysqli_query($conn, $master_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Control Center | Al Shifa Medical Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; color: #333; }
        .sidebar { background: #1e293b; min-height: 100vh; color: #fff; display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .stat-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .table-responsive-wrapper { background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .screenshot-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; cursor: pointer; }
        .logout-btn { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .logout-btn:hover { background: #ef4444; color: #fff; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-flex">
            <div class="w-100">
                <div class="d-flex align-items-center mb-4 px-2 pt-2">
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
            
            <div class="w-100 pt-3 border-top border-secondary">
                <a href="logout.php" class="btn logout-btn w-100 rounded-3 py-2 fw-bold shadow-sm transition">
                    <i class="bi bi-box-arrow-left me-2"></i> Sign Out
                </a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold m-0">System Control Desk</h2>
                    <p class="text-muted small m-0">Real-time revenue monitoring and financial ledger verification workspace.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="logout.php" class="btn btn-sm btn-outline-danger d-md-none rounded-3 fw-bold"><i class="bi bi-box-arrow-left"></i> Logout</a>
                    <div class="bg-white px-3 py-2 rounded-3 shadow-sm text-end border">
                        <span class="d-block small text-muted">Server Core Status</span>
                        <strong class="text-success"><i class="bi bi-cpu-fill me-1"></i> Live Online</strong>
                    </div>
                </div>
            </div>

            <?php echo $action_message; ?>

            <div class="row g-3 mb-5">
                <div class="col-sm-6 col-lg-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Total Revenue</span>
                                <h3 class="fw-bold m-0 mt-1 text-success">Rs. <?php echo number_format($total_earnings); ?></h3>
                            </div>
                            <div class="bg-success-subtle p-3 rounded-3 text-success"><i class="bi bi-cash-coin fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Pending Slips</span>
                                <h3 class="fw-bold m-0 mt-1 text-warning"><?php echo $pending_slips; ?></h3>
                            </div>
                            <div class="bg-warning-subtle p-3 rounded-3 text-warning"><i class="bi bi-clock-history fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Active Doctors</span>
                                <h3 class="fw-bold m-0 mt-1 text-primary"><?php echo $total_doctors; ?></h3>
                            </div>
                            <div class="bg-primary-subtle p-3 rounded-3 text-primary"><i class="bi bi-person-heart fs-4"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Total Patients</span>
                                <h3 class="fw-bold m-0 mt-1 text-dark"><?php echo $total_patients; ?></h3>
                            </div>
                            <div class="bg-dark-subtle p-3 rounded-3 text-dark"><i class="bi bi-people-fill fs-4"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Remittance Audit & Verification Desk</h4>
            <div class="table-responsive-wrapper p-3 bg-white">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Patient Identity</th>
                            <th>Assigned Doctor</th>
                            <th>Transaction ID (TID)</th>
                            <th>Screenshot Proof</th>
                            <th>Ledger Status</th>
                            <th class="text-center">Verification Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($appointments_result) > 0) {
                            while($row = mysqli_fetch_assoc($appointments_result)) {
                                $db_screenshot_val = trim($row['payment_screenshot'] ?? '');
                                $calculated_path = "";
                                if (!empty($db_screenshot_val) && $db_screenshot_val !== 'NULL') {
                                    if (file_exists($db_screenshot_val)) {
                                        $calculated_path = $db_screenshot_val;
                                    } elseif (file_exists("../" . $db_screenshot_val)) {
                                        $calculated_path = "../" . $db_screenshot_val;
                                    } else {
                                        $calculated_path = $db_screenshot_val;
                                    }
                                }
                        ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <span class="d-block fw-semibold text-dark"><?php echo htmlspecialchars($row['name'] ?? 'Guest'); ?></span>
                                <small class="text-muted d-block text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($row['problem'] ?? 'General Checkup'); ?></small>
                                <?php if (isset($row['patient_hidden']) && $row['patient_hidden'] == 1): ?>
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;"><i class="bi bi-eye-slash-fill"></i> Patient Deleted This</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary fw-bold"><?php echo htmlspecialchars($row['doctor']); ?></span></td>
                            <td>
                                <code class="text-dark fw-bold"><?php echo !empty($row['transaction_id']) ? htmlspecialchars($row['transaction_id']) : 'No TID Provided'; ?></code>
                            </td>
                            <td>
                                <?php if (!empty($calculated_path)): ?>
                                    <a href="<?php echo $calculated_path; ?>" target="_blank">
                                        <img src="<?php echo $calculated_path; ?>" class="screenshot-thumbnail" onerror="this.src='https://placehold.co/100x100?text=Error+Loading';">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="bi bi-x-circle me-1"></i> Missing Slip</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $row['fee_status'] ?? 'Pending';
                                if ($status == 'Confirmed' || $status == 'Paid') {
                                    echo "<span class='badge bg-success'><i class='bi bi-check-circle-fill me-1'></i> Confirmed</span>";
                                } elseif ($status == 'Pending Verification') {
                                    echo "<span class='badge bg-warning text-dark'><i class='bi bi-hourglass-split me-1'></i> Pending Audit</span>";
                                } elseif ($status == 'Rejected') {
                                    echo "<span class='badge bg-danger'><i class='bi bi-exclamation-triangle-fill me-1'></i> Rejected</span>";
                                } else {
                                    echo "<span class='badge bg-secondary'>$status</span>";
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <form method="POST" class="d-inline-flex gap-1">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                    
                                    <button type="submit" name="update_status" value="Confirmed" class="btn btn-sm btn-success px-2 py-1" title="Approve Payment" <?php if($status == 'Confirmed') echo 'disabled'; ?>>
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                    
                                    <button type="submit" name="update_status" value="Rejected" class="btn btn-sm btn-outline-danger px-2 py-1" title="Reject Payment" <?php if($status == 'Rejected') echo 'disabled'; ?>>
                                        <i class="bi bi-x"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No operational appointment logs registered in database.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>