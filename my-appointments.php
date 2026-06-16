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

// --- ACTION CONTROLLER: APPROVE OR REJECT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appt_id = (int)$_POST['appointment_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status_value']);
    
    $update_query = "UPDATE appointments SET fee_status = '$new_status' WHERE id = $appt_id";
    mysqli_query($conn, $update_query);
    
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['status_filter']) ? "?status_filter=" . urlencode($_GET['status_filter']) : ""));
    exit();
}

// --- AUTO-SUBMIT DYNAMIC FILTER CONTROLLER ---
$filter_condition = "WHERE 1=1";
$selected_filter = '';

if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
    $selected_filter = mysqli_real_escape_string($conn, $_GET['status_filter']);
    $filter_condition .= " AND a.fee_status = '$selected_filter'";
}

// FIX 1: LEFT JOIN lagaya hai taake doctor_id ki jagah doctor ka real name doctors table se aaye
$appointments_query = "SELECT a.*, d.name AS doctor_name 
                       FROM appointments a 
                       LEFT JOIN doctors d ON a.doctor_id = d.id 
                       $filter_condition 
                       ORDER BY a.id DESC";
$res_appointments = mysqli_query($conn, $appointments_query);

// Dropdown ko dynamic rakhne ke liye database se unique statuses nikalna
$status_options_query = "SELECT DISTINCT fee_status FROM appointments WHERE fee_status IS NOT NULL AND fee_status != ''";
$res_statuses = mysqli_query($conn, $status_options_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Patients Appointments | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background: #1a252f; min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #b8c7ce; font-weight: 500; border-radius: 5px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #34495e; color: #fff; }
        .main-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .table th { background-color: #f1f3f5; color: #495057; font-weight: 600; padding: 15px; }
        .table td { padding: 15px; vertical-align: middle; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center mb-4 px-2">
                <i class="bi bi-heart-pulse-fill text-danger fs-3 me-2"></i>
                <h5 class="m-0 fw-bold">Al Shifa Clinic</h5>
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

        <!-- Main Content Window -->
        <div class="col-md-9 col-lg-10 p-4">
            
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold text-dark m-0">All Patients Appointments List</h2>
                    <p class="text-muted m-0">Yahan clinic ke tamam mareezon ki details aur appointments ki list mojood hai.</p>
                </div>
                
                <!-- FIX 2: onchange event lagaya hai, ab click karte hi auto-filter hoga -->
                <form method="GET" id="filterForm" class="bg-white p-2 rounded shadow-sm border">
                    <label class="small text-muted fw-bold d-block mb-1">Filter by Status:</label>
                    <select name="status_filter" class="form-select form-select-sm border-0 bg-light" style="width: 240px;" onchange="this.form.submit();">
                        <option value="">Show All (Sab Appointments)</option>
                        <?php 
                        if ($res_statuses && mysqli_num_rows($res_statuses) > 0) {
                            while($status_row = mysqli_fetch_assoc($res_statuses)) {
                                $opt = $status_row['fee_status'];
                                
                                // User ke samajhne ke liye easy Urdu/English labels
                                $display_name = $opt;
                                if ($opt === 'Confirmed' || $opt === 'Paid') $display_name = "Confirmed (Fees Paid)";
                                if ($opt === 'Pending Verification') $display_name = "Pending (Waiting Check)";
                                if ($opt === 'Rejected') $display_name = "Rejected (Cancelled)";
                                
                                $selected = ($selected_filter === $opt) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($opt)."' $selected>".htmlspecialchars($display_name)."</option>";
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>

            <!-- Main Patient Data Table Card -->
            <div class="card main-card p-4 bg-white">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle m-0">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Token No</th>
                                <th>Patient Details</th>
                                <th>Doctor Name</th>
                                <th>Appointment Date & Time</th>
                                <th>Current Status</th>
                                <th style="width: 180px; text-align: center;">Actions (Change Status)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($res_appointments) > 0) {
                                while ($row = mysqli_fetch_assoc($res_appointments)):
                            ?>
                            <tr>
                                <!-- Token ID -->
                                <td>
                                    <span class="badge bg-dark px-2 py-2 fs-6">#<?php echo $row['id']; ?></span>
                                </td>
                                
                                <!-- Patient Info -->
                                <td>
                                    <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($row['name'] ?? 'No Name Provided'); ?></div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-telephone-fill me-1"></i> <?php echo htmlspecialchars($row['phone'] ?? 'No Phone Contact'); ?> 
                                        <?php if(!empty($row['email'])): ?>
                                            | <i class="bi bi-envelope-fill me-1"></i> <?php echo htmlspecialchars($row['email']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-2 rounded bg-light border-start border-primary border-3 small text-secondary mt-1">
                                        <strong>Problem/Disease:</strong> <?php echo htmlspecialchars($row['problem'] ?? 'General Checkup'); ?>
                                    </div>
                                </td>
                                
                                <!-- Doctor Name (Ab ID ki jagah name show hoga) -->
                                <td>
                                    <div class="text-primary fw-semibold fs-6">
                                        <i class="bi bi-person-heart me-1"></i> 
                                        <?php echo htmlspecialchars($row['doctor_name'] ?? 'Not Assigned'); ?>
                                    </div>
                                </td>
                                
                                <!-- Exact Appointment Date Requested by Patient -->
                                <td>
                                    <div class="text-dark fw-bold">
                                        <i class="bi bi-calendar-check-fill me-1 text-primary"></i> 
                                        <?php 
                                            echo !empty($row['app_date']) ? date('d-M-Y h:i A', strtotime($row['app_date'])) : 'No Target Date set'; 
                                        ?>
                                    </div>
                                </td>
                                
                                <!-- Current Status Badges -->
                                <td>
                                    <?php 
                                    $st = $row['fee_status'] ?? 'Pending';
                                    if($st == 'Confirmed' || $st == 'Paid') {
                                        echo "<span class='badge bg-success p-2 w-100'><i class='bi bi-check-circle-fill me-1'></i> Fees Paid</span>";
                                    } elseif($st == 'Pending Verification') {
                                        echo "<span class='badge bg-warning text-dark p-2 w-100'><i class='bi bi-hourglass-split me-1'></i> Waiting Check</span>";
                                    } elseif($st == 'Rejected') {
                                        echo "<span class='badge bg-danger p-2 w-100'><i class='bi bi-x-circle-fill me-1'></i> Cancelled</span>";
                                    } else {
                                        echo "<span class='badge bg-secondary p-2 w-100'>$st</span>";
                                    }
                                    ?>
                                </td>
                                
                                <!-- QUICK ACTION BUTTONS -->
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <!-- Approve Button -->
                                        <form method="POST" action="" onsubmit="return confirm('Kya aap is appointment ko approve karna chahte hain?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="status_value" value="Confirmed">
                                            <button type="submit" name="update_status" class="btn btn-sm btn-outline-success w-100 fw-medium">
                                                <i class="bi bi-check2"></i> Approve (Pass)
                                            </button>
                                        </form>
                                        
                                        <!-- Reject Button -->
                                        <form method="POST" action="" onsubmit="return confirm('Kya aap is appointment ko reject/cancel karna chahte hain?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="status_value" value="Rejected">
                                            <button type="submit" name="update_status" class="btn btn-sm btn-outline-danger w-100 fw-medium">
                                                <i class="bi bi-trash3"></i> Reject (Fail)
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5 text-muted fs-5'>Koi records ya appointments nahi milein.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>