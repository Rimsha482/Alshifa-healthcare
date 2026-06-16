<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");
if (!$conn) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

// Session Check
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_id = intval($_SESSION['user_id']);

// --- 1. FETCH LOGGED-IN USER INFO ---
$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_res);
$user_email = mysqli_real_escape_string($conn, $user_data['email'] ?? '');


// --- 2. EXPORT LOGIC ---
if (isset($_GET['export'])) {
    if (ob_get_length()) ob_end_clean();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=medical_report_ID_' . $user_id . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Doctor', 'Date', 'Problem', 'Prescription', 'Status', 'Fee', 'Meeting Link'));
    
    $export_query = "SELECT a.id, a.doctor, a.app_date, a.problem, a.prescription_text, a.status, a.fee_status, a.meeting_link 
                     FROM appointments a 
                     INNER JOIN users u ON a.email = u.email 
                     WHERE u.id = $user_id AND a.patient_hidden = 0 
                     ORDER BY a.app_date DESC";
                     
    $rows = mysqli_query($conn, $export_query);
    
    if ($rows) {
        while ($row = mysqli_fetch_assoc($rows)) {
            fputcsv($output, array(
                $row['id'],
                $row['doctor'] ?? 'N/A',
                $row['app_date'] ?? 'N/A',
                $row['problem'] ?? 'N/A',
                $row['prescription_text'] ?? 'No advice yet',
                $row['status'] ?? 'Pending',
                $row['fee_status'] ?? 'Unpaid',
                $row['meeting_link'] ?? 'No Link'
            ));
        }
        mysqli_free_result($rows);
    }
    
    fclose($output);
    exit();
}


// --- 3. RECEIPT UPLOAD LOGIC ---
// --- 3. RECEIPT UPLOAD LOGIC ---
$upload_error = "";
$upload_success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_screenshot'])) {
    $app_id = intval($_POST['appointment_id']);
    
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "pdf" => "application/pdf");
        $filename = $_FILES['screenshot']['name'];
        $filetype = $_FILES['screenshot']['type'];
        $filesize = $_FILES['screenshot']['size'];
    
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed)) {
            $upload_error = "Error: Please select a valid file format (JPEG/JPG/PNG/PDF).";
        }
    
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $upload_error = "Error: File size is larger than the allowed limit (5MB).";
        }
    
        if (empty($upload_error) && in_array($filetype, $allowed)) {
            $target_dir = "uploads/screenshots/";
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            
            // Filename se spaces aur extra characters clean kiye
            $clean_filename = preg_replace("/[^a-zA-Z0-9._-]/", " ", $filename);
            $new_filename = time() . "_" . $clean_filename;
            
            // Yeh variable pura path database me save karwayega
            $database_stored_path = $target_dir . $new_filename; 
            
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $database_stored_path)) {
                // Ab database me $new_filename ki jagah $database_stored_path save hoga
                $stmt = $conn->prepare("UPDATE appointments SET fee_status = 'Pending Verification', payment_screenshot = ? WHERE id = ?");
                $stmt->bind_param("si", $database_stored_path, $app_id);
                if ($stmt->execute()) {
                    $upload_success = "Receipt uploaded successfully! Awaiting doctor verification.";
                } else {
                    $upload_error = "Database update failed.";
                }
                $stmt->close();
            } else {
                $upload_error = "File could not be uploaded. Check folder permissions.";
            }
        }
    } else {
        $upload_error = "Error: System could not process file upload.";
    }
}


// --- 4. FETCH RECORDS FOR DASHBOARD (WITH SOFT DELETE FILTER) ---
$query_appointments = "SELECT a.* FROM appointments a 
                       INNER JOIN users u ON a.email = u.email 
                       WHERE u.id = $user_id AND a.patient_hidden = 0 
                       ORDER BY a.app_date DESC";

$appointments = mysqli_query($conn, $query_appointments);

$latest_app_q = mysqli_query($conn, "SELECT phone, email FROM appointments WHERE email = '$user_email' ORDER BY id DESC LIMIT 1");
$latest_app = mysqli_fetch_assoc($latest_app_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal | Al Shifa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --p-accent: #6366f1; --s-bg: #0f172a; }
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; overflow-x: hidden; }

        .mobile-header { display: none; background: var(--s-bg); color: white; padding: 1rem 1.5rem; position: sticky; top: 0; z-index: 1050; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        .sidebar { width: 280px; height: 100vh; background: var(--s-bg); position: fixed; top: 0; left: 0; padding: 2.5rem 1.5rem; color: white; z-index: 1000; box-shadow: 10px 0 30px rgba(0,0,0,0.05); transition: all 0.3s ease; }
        .main-content { margin-left: 280px; padding: 3rem; transition: all 0.3s ease; min-height: 100vh; }

        .nav-link { color: #94a3b8; padding: 1.1rem; border-radius: 16px; margin-bottom: 0.6rem; display: flex; align-items: center; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: rgba(99, 102, 241, 0.1); color: #818cf8; }
        .nav-link i { font-size: 1.3rem; margin-right: 15px; }

        .table-box { background: white; border-radius: 24px; padding: 2.5rem; border: 1px solid #f1f5f9; box-shadow: 0 15px 50px rgba(0,0,0,0.02); }
        .table thead th { background: #f8fafc; border: none; color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; padding: 1.5rem 1rem; white-space: nowrap; }
        .table tbody td { padding: 1.5rem 1rem; border-bottom: 1px solid #f8fafc; white-space: nowrap; }

        .status-pill { padding: 8px 16px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; }
        
        .btn-eye { background: #f1f5f9; color: #6366f1; border: none; width: 42px; height: 42px; border-radius: 12px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .btn-eye:hover { background: #6366f1; color: white; transform: scale(1.06); }

        .btn-meet { padding: 8px 16px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; }
        .btn-meet-active { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .btn-meet-active:hover { background: #4338ca; color: white; }
        .btn-meet-disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; border: 1px solid #e2e8f0; }

        .action-dropdown .btn-action {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 12px;
            padding: 7px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }

        .action-dropdown .btn-action:hover, 
        .action-dropdown .btn-action[aria-expanded="true"] {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .action-dropdown .dropdown-toggle::after {
            vertical-align: middle;
            margin-left: 6px;
            color: #94a3b8;
        }

        .action-dropdown .dropdown-menu {
            border-radius: 16px;
            padding: 8px;
            min-width: 190px;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        }

        .action-dropdown .dropdown-item {
            color: #334155;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.15s ease;
        }

        .action-dropdown .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .action-dropdown .dropdown-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .action-dropdown .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .brand-text { font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; }

        @media (max-width: 991.98px) {
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .table-box { padding: 1.5rem; border-radius: 16px; }
            
            header.d-flex { flex-direction: column !important; gap: 1rem; }
            header.d-flex a { width: 100%; text-align: center; display: block; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <h4 class="brand-text text-white mb-0">Al Shifa<span class="text-primary">.</span></h4>
    <button class="btn btn-outline-light border-0 fs-3 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
        <i class="bi bi-list"></i>
    </button>
</div>

<aside class="sidebar d-none d-lg-flex flex-column">
    <div class="mb-5 px-3">
        <h4 class="brand-text text-white mb-0">Al Shifa<span class="text-primary">.</span></h4>
        <p class="text-muted small">v2.1 Premium</p>
    </div>
    <nav class="flex-grow-1">
        <a href="patient-dashboard.php" class="nav-link active"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="bi bi-person-circle"></i> My Information</a>
    </nav>
    <div class="pt-4 border-top border-secondary">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout Account</a>
    </div>
</aside>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" style="width: 280px; background: var(--s-bg) !important;">
    <div class="offcanvas-header px-4 pt-4">
        <div>
            <h4 class="brand-text text-white mb-0">Al Shifa<span class="text-primary">.</span></h4>
            <p class="text-muted small mb-0">v2.1 Premium</p>
        </div>
        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-3 flex-column d-flex justify-content-between">
        <nav class="flex-grow-1">
            <a href="patient-dashboard.php" class="nav-link active" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas')).hide();"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#profileModal" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas')).hide();"><i class="bi bi-person-circle"></i> My Information</a>
            <a href="#medical-history" class="nav-link" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('sidebarOffcanvas')).hide();"><i class="bi bi-file-medical"></i> View Prescriptions</a>
        </nav>
        <div class="pt-4 border-top border-secondary mb-3">
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout Account</a>
        </div>
    </div>
</div>

<main class="main-content">
    <?php if(!empty($upload_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $upload_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(!empty($upload_success)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $upload_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <header class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">Medical Record</h2>
            <p class="text-muted">Welcome back, <b><?php echo htmlspecialchars($user_data['name'] ?? 'Patient Account'); ?></b></p>
        </div>
        <a href="patient-dashboard.php?export=1" class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow">
            <i class="bi bi-file-earmark-arrow-down me-2"></i> Download CSV
        </a>
    </header>

    <div class="table-box" id="medical-history">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
            <h5 class="fw-bold mb-0">Appointment History</h5>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">Last Updated: <?php echo date('d M Y'); ?></span>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Consultant / Date</th>
                        <th>Meeting Status</th>
                        <th>Fee Status</th>
                        <th>Meeting Link</th>
                        <th>Prescription</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($appointments && mysqli_num_rows($appointments) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['doctor'] ?? 'General Practitioner'); ?></div>
                            <small class="text-muted">
                                <?php echo !empty($row['app_date']) ? date('D, d M Y', strtotime($row['app_date'])) : 'Date Unscheduled'; ?>
                            </small>
                        </td>
                        <td>
                            <span class="status-pill <?php echo (isset($row['status']) && $row['status'] == 'Completed') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if(isset($row['fee_status']) && $row['fee_status'] == 'Paid'): ?>
                                <span class="fw-bold text-success">
                                    <i class="bi bi-cash-stack me-1"></i> Paid
                                </span>
                            <?php elseif(isset($row['fee_status']) && $row['fee_status'] == 'Pending Verification'): ?>
                                <span class="badge bg-info-subtle text-info border rounded-pill px-2 py-1 small">
                                    <i class="bi bi-hourglass-split me-1"></i> Verifying Proof
                                </span>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1 align-items-start">
                                    <span class="fw-bold text-danger">
                                        <i class="bi bi-x-circle me-1"></i> Unpaid
                                    </span>
                                    <button class="btn btn-link p-0 text-decoration-underline text-primary small fw-semibold" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#uploadReceiptModal<?php echo $row['id']; ?>">
                                        <i class="bi bi-cloud-arrow-up"></i> Upload Screenshot
                                    </button>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                           <?php if(!empty($row['meeting_link'])): ?>
                                <a href="<?php echo htmlspecialchars($row['meeting_link']); ?>" 
                                   target="_blank" 
                                   class="btn-meet btn-meet-active" 
                                   onclick="updateJoinStatus(<?php echo $row['id']; ?>)">
                                     <i class="bi bi-camera-video-fill me-1"></i> Join Meeting
                                </a>
                            <?php else: ?>
                                <span class="btn-meet btn-meet-disabled">
                                    <i class="bi bi-clock-history me-1"></i> Link Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <button class="btn-eye" data-bs-toggle="modal" data-bs-target="#viewPresc<?php echo $row['id']; ?>">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </td>
                        
                        <td class="text-center">
                            <div class="dropdown action-dropdown">
                                <button class="btn btn-action dropdown-toggle shadow-sm d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear-fill text-muted"></i> <span>Manage</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    <?php if(isset($row['fee_status']) && $row['fee_status'] == 'Unpaid'): ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#" data-bs-toggle="modal" data-bs-target="#uploadReceiptModal<?php echo $row['id']; ?>">
                                            <i class="bi bi-receipt text-warning fs-6"></i> 
                                            <span>Submit Payment Info</span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-2 opacity-50"></li>
                                    <?php endif; ?>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="delete_appointment.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to remove this record from your dashboard?');">
                                            <i class="bi bi-trash3-fill fs-6"></i> 
                                            <span>Delete Record</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="viewPresc<?php echo $row['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-md">
                            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                <div class="modal-header bg-dark text-white p-4">
                                    <h6 class="modal-title fw-bold">Doctor's Advice</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 p-md-5">
                                    <div class="text-center mb-4">
                                        <div class="p-3 bg-light rounded-circle d-inline-block mb-2">
                                            <i class="bi bi-capsule-pill text-primary fs-2"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0">Dr. <?php echo htmlspecialchars($row['doctor'] ?? 'Medical Specialist'); ?></h5>
                                        <p class="text-muted small"><?php echo !empty($row['app_date']) ? date('d M Y', strtotime($row['app_date'])) : ''; ?></p>
                                    </div>
                                    <div class="bg-light p-4 rounded-4 border-start border-primary border-4 shadow-sm">
                                        <p class="mb-0 text-dark" style="font-style: italic; line-height: 1.6; word-break: break-word;">
                                            "<?php echo !empty($row['prescription_text']) ? htmlspecialchars($row['prescription_text']) : "Doctor has not updated the advice yet."; ?>"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="uploadReceiptModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header bg-dark text-white p-4">
                                    <h5 class="modal-title fw-bold fs-6"><i class="bi bi-shield-exclamation text-warning me-2"></i>Resolve Payment Conflict</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                                    <div class="modal-body p-4">
                                        <p class="text-muted small mb-3">
                                            Agar aapne fee pay kar di hai par yahan <b>Unpaid</b> araha hai, to verification screenshot upload karein taakay doctor ise verify kar sakein.
                                        </p>
                                        <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">Select Payment Receipt File</label>
                                            <input type="file" name="screenshot" class="form-control rounded-3" accept="image/*,application/pdf" required>
                                            <div class="form-text text-muted" style="font-size:0.75rem;">Formats: PNG, JPG, JPEG, PDF (Max: 5MB)</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0 p-3 rounded-bottom-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="upload_screenshot" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Submit Receipt</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                            No appointments found for your account.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-body p-4 p-md-5 text-center">
                <div class="bg-primary-subtle text-primary p-4 rounded-circle d-inline-block mb-3 shadow-sm">
                    <i class="bi bi-shield-lock fs-1"></i>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user_data['name'] ?? 'Patient Profile'); ?></h4>
                <p class="text-muted mb-4 small">Secure Patient Profile</p>
                
                <div class="text-start bg-light p-3 p-md-4 rounded-4">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted small">System ID</span>
                        <span class="fw-bold">#AS-0<?php echo $user_id; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted small">Contact Number</span>
                        <span class="fw-bold text-primary text-break">
                            <?php echo htmlspecialchars($latest_app['phone'] ?? ($user_data['phone'] ?? 'Update needed')); ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap gap-1">
                        <span class="text-muted small">Login Email</span>
                        <span class="fw-bold small text-break"><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                <button class="btn btn-dark w-100 mt-4 py-3 rounded-pill fw-bold" data-bs-dismiss="modal">Go Back</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateJoinStatus(appointmentId) {
    fetch('update_status.php?id=' + appointmentId)
    .then(response => response.text())
    .then(data => { console.log("Status Updated: " + data); })
    .catch(err => console.error("Error updating status: ", err));
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>