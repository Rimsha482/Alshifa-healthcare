<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");
if (!$conn) { die("Database Connection failed: " . mysqli_connect_error()); }

// Admin Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. DYNAMIC ADMIN DATA FETCHING
$admin_id = intval($_SESSION['user_id']); 
$admin_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $admin_id");
$admin_data = mysqli_fetch_assoc($admin_query);

// Wahi Professional Medical Icon jo admin.php mein hai
$admin_img = "https://cdn-icons-png.flaticon.com/512/3304/3304567.png"; 

// 2. FETCH ALL APPOINTMENTS DATA (FIXED WITH EXACT COLUMNS)
// Aapki list ke mutabiq exact columns `meeting_link` aur `videoLink` ko fetch kar liya hai
$query = "SELECT a.id, a.doctor, a.app_date, a.problem, a.status, a.fee_status, a.prescription_text, 
                 a.meeting_link, a.videoLink, u.name as patient_name, u.id as p_id 
          FROM appointments a 
          INNER JOIN users u ON a.email = u.email 
          ORDER BY a.app_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments | Al Shifa Medical Centre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --primary-dark: #0f172a; --accent: #6366f1; --medical-blue: #0ea5e9; }
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        
        /* Mobile Sticky Header */
        .mobile-header {
            display: none;
            background: var(--primary-dark);
            color: white;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Sidebar Desktop Framework */
        .sidebar { background: var(--primary-dark); height: 100vh; color: white; padding: 30px 20px; position: fixed; width: 260px; transition: 0.3s; z-index: 1000; left: 0; top: 0; }
        .main-content { margin-left: 260px; padding: 40px; transition: all 0.3s ease; min-height: 100vh; }
        
        .nav-link { color: #94a3b8; border-radius: 12px; margin-bottom: 10px; padding: 12px 15px; transition: 0.3s; text-decoration: none; display: block; }
        .nav-link:hover, .nav-link.active { background: rgba(99, 102, 241, 0.1); color: #818cf8; }
        
        .admin-box { background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; margin-bottom: 30px; border: 1px solid rgba(255,255,255,0.1); }
        .admin-img-nav { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 2px solid var(--medical-blue); background: white; padding: 2px; }
        
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); overflow: hidden; }
        .badge-status { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; display: inline-block; }

        .table th, .table td { white-space: nowrap; }

        @media (max-width: 991.98px) {
            .mobile-header { display: flex; justify-content: space-between; align-items: center; }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0; padding: 20px; }
            
            .header-panel {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1.5rem;
            }
            .header-panel .admin-profile-badge { width: 100%; justify-content: space-between; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <h4 class="fw-bold text-white mb-0">Al Shifa<span class="text-info">.</span></h4>
    <button class="btn btn-outline-light border-0 fs-3 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#appointmentsOffcanvasSidebar">
        <i class="bi bi-list"></i>
    </button>
</div>

<div class="sidebar">
    <div class="px-3 mb-5">
        <h4 class="fw-bold text-white mb-0">Al Shifa<span class="text-info">.</span></h4>
        <small class="text-muted">Medical Management</small>
    </div>
    
    <div class="admin-box d-flex align-items-center">
        <img src="<?php echo $admin_img; ?>" class="admin-img-nav me-3 shadow-sm">
        <div>
            <div class="fw-bold small text-white"><?php echo htmlspecialchars($admin_data['name'] ?? 'Admin'); ?></div>
            <div class="text-info" style="font-size: 10px; letter-spacing: 1px;">ONLINE</div>
        </div>
    </div>

    <ul class="nav flex-column mt-4">
        <li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-person-badge-fill me-2"></i> Doctors Management</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin-appointments.php"><i class="bi bi-calendar2-check-fill me-2"></i> All Appointments</a></li>
        <li class="nav-item mt-5"><a class="nav-link text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
    </ul>
</div>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="appointmentsOffcanvasSidebar" style="width: 260px; background: var(--primary-dark) !important;">
    <div class="offcanvas-header px-4 pt-4">
        <div>
            <h4 class="fw-bold text-white mb-0">Al Shifa<span class="text-info">.</span></h4>
            <small class="text-muted">Medical Management</small>
        </div>
        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-3 d-flex flex-column justify-content-between">
        <div>
            <div class="admin-box d-flex align-items-center mt-3 mx-2">
                <img src="<?php echo $admin_img; ?>" class="admin-img-nav me-3 shadow-sm">
                <div>
                    <div class="fw-bold small text-white"><?php echo htmlspecialchars($admin_data['name'] ?? 'Admin'); ?></div>
                    <div class="text-info" style="font-size: 10px; letter-spacing: 1px;">ONLINE</div>
                </div>
            </div>
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">
                        <i class="bi bi-person-badge-fill me-2"></i> Doctors Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin-appointments.php">
                        <i class="bi bi-calendar2-check-fill me-2"></i> All Appointments
                    </a>
                </li>
            </ul>
        </div>
        <div class="mb-4 px-2">
            <a class="nav-link text-danger fw-bold border-top border-secondary pt-3 rounded-0" href="logout.php">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5 header-panel">
        <div>
            <h2 class="fw-bold text-dark mb-1">Appointment Records</h2>
            <p class="text-muted small mb-0">View and track all patient consultations.</p>
        </div>
        <div class="d-flex align-items-center bg-white p-2 px-3 shadow-sm rounded-pill border admin-profile-badge">
            <span class="me-3 fw-semibold text-dark small"><?php echo htmlspecialchars($admin_data['name'] ?? 'Admin'); ?></span>
            <img src="<?php echo $admin_img; ?>" class="admin-img-nav" style="width: 35px; height: 35px;">
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Master Appointment List</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-4">PATIENT INFO</th>
                        <th>ASSIGNED DOCTOR</th>
                        <th>JOIN STATUS</th>
                        <th>VISIT STATUS</th>
                        <th>FEE STATUS</th>
                        <th>PRESCRIPTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['patient_name']); ?></div>
                                <small class="text-muted">ID: #<?php echo intval($row['p_id']); ?></small>
                            </td>
                            <td><div class="text-primary fw-semibold"><?php echo htmlspecialchars($row['doctor']); ?></div></td>
                            
                            <td>
                                <?php 
                                // Hum check karte hain ke kya dono me se kisi ek column me link maujud hai?
                                $m_link = !empty($row['meeting_link']) ? $row['meeting_link'] : ($row['videoLink'] ?? '');

                                if(!empty($m_link) && strlen(trim($m_link)) > 5): ?>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill d-inline-block align-self-start">
                                            <i class="bi bi-link-45deg"></i> Link Active
                                        </span>
                                        
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                        <i class="bi bi-dash-circle"></i> Waiting for Dr.
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge-status <?php echo ($row['status'] == 'Completed') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><span class="fw-bold <?php echo ($row['fee_status'] == 'Paid') ? 'text-success' : 'text-danger'; ?>"><?php echo htmlspecialchars($row['fee_status']); ?></span></td>
                            <td>
                                <small class="text-muted"><?php echo !empty($row['prescription_text']) ? htmlspecialchars(substr($row['prescription_text'], 0, 20))."..." : "Pending"; ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted font-italic">No appointments recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>