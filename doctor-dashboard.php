<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// Security layer: Check if doctor is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = intval($_SESSION['user_id']);

// --- 1. EXPORT TO CSV ---
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=appointments_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Patient Name', 'Email', 'Date', 'Problem', 'Status', 'Fee Status'));
    $rows = mysqli_query($conn, "SELECT id, name, email, app_date, problem, status, fee_status FROM appointments WHERE doctor_id = $doctor_id");
    while ($row = mysqli_fetch_assoc($rows)) fputcsv($output, $row);
    exit();
}

// --- 2. DELETE RECORD LOGIC ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM appointments WHERE id = $delete_id AND doctor_id = $doctor_id");
    header("Location: doctor-dashboard.php?msg=deleted");
    exit();
}

// --- 3. DOCTOR INFO ---
$dr_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $doctor_id");
$dr_info = mysqli_fetch_assoc($dr_query);

// --- 4. NEW COUNTERS (TOTAL, PENDING, COMPLETED) ---
$total = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM appointments WHERE doctor_id = $doctor_id"));
$pending = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM appointments WHERE doctor_id = $doctor_id AND status = 'Pending'"));
$completed = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM appointments WHERE doctor_id = $doctor_id AND status = 'Completed'"));

// --- 5. UPDATE APPOINTMENT PROCESSOR (Saves Status & Fee Status directly to Backend) ---
if (isset($_POST['update_appointment'])) {
    $app_id = intval($_POST['app_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $fee_s = mysqli_real_escape_string($conn, $_POST['fee_status']); // Form se Fee Status uthaya
    $m_link = mysqli_real_escape_string($conn, $_POST['meeting_link']);
    $presc = mysqli_real_escape_string($conn, $_POST['prescription_text']);

    // Query running directly to update everything in the specific row
    mysqli_query($conn, "UPDATE appointments SET status = '$status', fee_status = '$fee_s', meeting_link = '$m_link', prescription_text = '$presc' WHERE id = $app_id AND doctor_id = $doctor_id");
    header("Location: doctor-dashboard.php?msg=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Dr. Dashboard | Al Shifa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #111827;
            --primary-accent: #6366f1;
            --glass-bg: rgba(255, 255, 255, 0.9);
        }

        body { 
            background: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            overflow-x: hidden;
        }

        .mobile-header {
            display: none;
            background: var(--sidebar-bg);
            color: white;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 10px 0 30px rgba(0,0,0,0.05);
            z-index: 1000;
        }

        .main-content {
            margin-left: 280px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .nav-link {
            color: #94a3b8;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            text-decoration: none;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-accent);
        }

        .nav-link i { font-size: 1.25rem; margin-right: 12px; }

        .clickable-card {
            text-decoration: none !important;
            color: inherit !important;
            display: block;
        }

        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.6rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01);
        }

        .clickable-card:hover .stat-card { 
            transform: translateY(-5px); 
            box-shadow: 0 12px 20px -5px rgba(0,0,0,0.05);
            border-color: var(--primary-accent);
        }
        
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .table-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }

        .table thead th {
            background: #f8fafc;
            border: none;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1.2rem 1rem;
            white-space: nowrap;
        }

        .table tbody tr { transition: 0.2s; border-bottom: 1px solid #f1f5f9; }
        .table tbody tr:hover { background: #fdfdfd; }
        .table tbody td { padding: 1.2rem 1rem; white-space: nowrap; }

        .btn-modern {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            transition: 0.3s;
        }

        .modal-content { border-radius: 24px; border: none; overflow: hidden; }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.5rem 2rem; }

        .slip-img-preview {
            max-height: 380px;
            width: 100%;
            object-fit: contain;
            background-color: #f8f9fa;
        }

        @media (max-width: 991.98px) {
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .table-container { padding: 1.5rem; border-radius: 16px; }
            
            header.d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1.25rem;
            }
            header.d-flex a.btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <h4 class="fw-bold text-white mb-0">AL-SHIFA<span class="text-primary">.</span></h4>
    <button class="btn btn-outline-light border-0 fs-3 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#doctorSidebarOffcanvas">
        <i class="bi bi-list"></i>
    </button>
</div>

<aside class="sidebar d-flex flex-column">
    <div class="mb-5 px-2">
        <h3 class="fw-bold text-white mb-0">AL-SHIFA <span class="text-primary">.</span></h3>
    </div>

    <div class="mb-4 text-center">
        <div class="position-relative d-inline-block">
            <img src="<?php echo htmlspecialchars($dr_info['image'] ?? 'assets/default-dr.png'); ?>" class="rounded-circle shadow" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid #312e81;">
        </div>
        <h6 class="text-white mt-3 mb-0 fw-bold"><?php echo htmlspecialchars($dr_info['name']); ?></h6>
        <p class="text-muted small"><?php echo htmlspecialchars($dr_info['specialization']); ?></p>
    </div>

    <nav class="flex-grow-1">
        <a href="doctor-dashboard.php" class="nav-link active"><i class="bi bi-columns-gap"></i> Dashboard</a>
        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#infoModal"><i class="bi bi-person-check"></i> My Account</a>
        <a href="doctor-dashboard.php?export=1" class="nav-link"><i class="bi bi-file-earmark-text"></i> Download Reports</a>
    </nav>

    <div class="pt-4 border-top border-secondary">
        <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</aside>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="doctorSidebarOffcanvas" style="width: 280px; background: var(--sidebar-bg) !important;">
    <div class="offcanvas-header px-4 pt-4">
        <h3 class="fw-bold text-white mb-0">AL-SHIFA <span class="text-primary">.</span></h3>
        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-3 flex-column d-flex justify-content-between">
        <div>
            <div class="mb-4 text-center mt-3">
                <img src="<?php echo htmlspecialchars($dr_info['image'] ?? 'assets/default-dr.png'); ?>" class="rounded-circle shadow" style="width: 85px; height: 85px; object-fit: cover; border: 3px solid #312e81;">
                <h6 class="text-white mt-3 mb-0 fw-bold"><?php echo htmlspecialchars($dr_info['name']); ?></h6>
                <p class="text-muted small"><?php echo htmlspecialchars($dr_info['specialization']); ?></p>
            </div>
            <nav class="flex-grow-1">
                <a href="doctor-dashboard.php" class="nav-link active" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('doctorSidebarOffcanvas')).hide();"><i class="bi bi-columns-gap"></i> Dashboard</a>
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#infoModal" onclick="bootstrap.Offcanvas.getInstance(document.getElementById('doctorSidebarOffcanvas')).hide();"><i class="bi bi-person-check"></i> My Account</a>
                <a href="doctor-dashboard.php?export=1" class="nav-link"><i class="bi bi-file-earmark-text"></i> Download Reports</a>
            </nav>
        </div>
        <div class="pt-4 border-top border-secondary mb-3">
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>
</div>

<main class="main-content">
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Record updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-trash-fill me-2"></i> Appointment records deleted safely.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Welcome <?php echo htmlspecialchars($dr_info['name']); ?>!</h4>
            <p class="text-muted small mb-0">Monitoring clinic performance and patients</p>
        </div>
        <div class="d-flex gap-3">
            <a href="doctor-dashboard.php?export=1" class="btn btn-outline-dark btn-modern shadow-sm">
                <i class="bi bi-cloud-arrow-down me-2"></i> Export Data
            </a>
        </div>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-12 col-md-4">
            <a href="doctor_filter.php?type=Total" class="clickable-card">
                <div class="stat-card">
                    <div><p class="text-muted small mb-1 fw-semibold">Total Consultations</p><h3 class="fw-bold mb-0"><?php echo $total; ?></h3></div>
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="doctor_filter.php?type=Pending" class="clickable-card">
                <div class="stat-card" style="border-left: 4px solid #ef4444;">
                    <div><p class="text-danger small mb-1 fw-semibold">Pending Approvals</p><h3 class="fw-bold mb-0 text-danger"><?php echo $pending; ?></h3></div>
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="doctor_filter.php?type=Completed" class="clickable-card">
                <div class="stat-card" style="border-left: 4px solid #10b981;">
                    <div><p class="text-success small mb-1 fw-semibold">Completed Visits</p><h3 class="fw-bold mb-0 text-success"><?php echo $completed; ?></h3></div>
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                </div>
            </a>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Appointment Schedule</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Patient Info</th>
                        <th>Date</th>
                        <th>Meeting Link</th>
                        <th>Status</th>
                        <th>Fee Verification</th>
                        <th>Fee Status</th>
                        <th class="text-center" style="width: 200px;">Actions Center</th>
                    </tr>
                </thead>
                <tbody>
                   <?php
$res = mysqli_query($conn, "SELECT * FROM appointments WHERE doctor_id = $doctor_id ORDER BY id DESC");
while($row = mysqli_fetch_assoc($res)):
    
    // --- SIMPLIFIED PATH ENGINE ---
    $screenshot_db_value = trim($row['payment_screenshot'] ?? ''); 
    $final_proof_file = "";
    $has_proof = false;

    if (!empty($screenshot_db_value) && $screenshot_db_value !== 'NULL') {
        
        // Agar database me pura path pehle se majood hai
        if (file_exists($screenshot_db_value)) {
            $final_proof_file = $screenshot_db_value;
            $has_proof = true;
        } 
        // Agar doctor file subfolder me hai, to check karein ke ../ lagane se file milti hai ya nahi
        elseif (file_exists("../" . $screenshot_db_value)) {
            $final_proof_file = "../" . $screenshot_db_value;
            $has_proof = true;
        } 
        // Fallback Protection
        else {
            $final_proof_file = $screenshot_db_value;
            $has_proof = true;
        }
    }
?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                            
                            <td>
                                <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            
                            <td><span class="text-muted small fw-semibold"><?php echo date('d M Y', strtotime($row['app_date'])); ?></span></td>
                            
                            <td>
                                <input type="url" name="meeting_link" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" value="<?php echo htmlspecialchars($row['meeting_link']?? ''); ?>" placeholder="Link status..." style="min-width: 140px;">
                            </td>
                            
                            <td>
                                <select name="status" class="form-select form-select-sm rounded-pill" style="min-width: 110px;">
                                    <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Completed" <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                </select>
                            </td>

                            <td>
                                <?php if ($has_proof): ?>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                            <i class="bi bi-file-earmark-image-fill"></i> Slip Detected
                                        </span>
                                        <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small fw-bold" data-bs-toggle="modal" data-bs-target="#slipModal<?php echo $row['id']; ?>">
                                            <i class="bi bi-eye-fill"></i> View Slip
                                        </button>
                                    </div>

                                    <div class="modal fade" id="slipModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                                <div class="modal-header border-0 bg-light rounded-top-4">
                                                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Payment Proof File (ID: #<?php echo $row['id']; ?>)</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center p-4">
                                                    <img src="<?php echo $final_proof_file; ?>" class="img-fluid rounded-3 shadow-sm border slip-img-preview" alt="Payment Proof File" onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Image+Not+Found+In+Folder';">
                                                    
                                                   
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">
                                            <i class="bi bi-exclamation-circle-fill"></i> No Fee Uploaded
                                        </span>
                                        <small class="text-muted font-monospace mt-1" style="font-size: 10px;">Screenshot field empty</small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <select name="fee_status" class="form-select form-select-sm rounded-pill fw-bold <?php echo ($row['fee_status'] == 'Paid') ? 'text-success bg-success-subtle' : 'text-danger bg-danger-subtle'; ?>" style="min-width: 100px;">
                                    <option value="Unpaid" <?php if($row['fee_status']=='Unpaid' || empty($row['fee_status'])) echo 'selected'; ?>>Unpaid</option>
                                    <option value="Paid" <?php if($row['fee_status']=='Paid') echo 'selected'; ?>>Paid</option>
                                </select>
                            </td>
                            
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center align-items-center">
                                    <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#prescModal<?php echo $row['id']; ?>" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;" title="Write E-Prescription">
                                        <i class="bi bi-journal-plus text-primary fs-5"></i>
                                    </button>
                                    
                                    <button type="submit" name="update_appointment" class="btn btn-primary btn-sm btn-modern px-3" style="font-size: 0.8rem;">
                                        <i class="bi bi-save me-1"></i> Update
                                    </button>
                                    
                                    <a href="doctor-dashboard.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm rounded-3 px-2 py-1" onclick="return confirm('Are you sure you want to completely erase this patient record?');" title="Delete Permanent Record">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </div>
                            </td>

                            <div class="modal fade" id="prescModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
                                    <div class="modal-content shadow-lg border-0">
                                        <div class="modal-header bg-dark text-white">
                                            <h6 class="fw-bold mb-0 text-white">E-Prescription: <?php echo htmlspecialchars($row['name'] ?? 'Patient'); ?></h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <textarea name="prescription_text" class="form-control border-0 bg-light p-3 rounded-3" rows="8" placeholder="Write prescription instructions down here..."><?php echo htmlspecialchars($row['prescription_text']?? ''); ?></textarea>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn type-button btn-sm btn-dark rounded-pill px-4" data-bs-dismiss="modal">Save & Close View</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="infoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mx-3 mx-sm-auto">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-4 p-md-5">
                <img src="<?php echo htmlspecialchars($dr_info['image'] ?? 'assets/default-dr.png'); ?>" class="rounded-circle mb-4 border border-5 border-light shadow" style="width:130px; height:130px; object-fit: cover;">
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($dr_info['name']); ?></h4>
                <span class="badge bg-primary-subtle text-primary mb-4 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($dr_info['specialization']); ?></span>
                
                <div class="text-start bg-light p-4 rounded-4">
                    <div class="mb-3 d-flex align-items-center">
                        <i class="bi bi-envelope text-muted me-3 fs-5"></i>
                        <div class="text-truncate">
                            <p class="mb-0 text-muted small">Registered Email</p>
                            <p class="mb-0 fw-bold text-dark text-break"><?php echo htmlspecialchars($dr_info['email']); ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-telephone text-muted me-3 fs-5"></i>
                        <div>
                            <p class="mb-0 text-muted small">Direct Phone</p>
                            <p class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($dr_info['phone'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-dark w-100 mt-4 btn-modern py-3 rounded-pill" data-bs-dismiss="modal">Close Profile</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>