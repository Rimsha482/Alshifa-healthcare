<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost", "root", "", "alshifa-db");
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctor_id = intval($_SESSION['user_id']);
$filter_type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : 'Total';

// Define dynamic conditional query bases on clicked counters
if ($filter_type === 'Pending') {
    $sql_condition = "AND status = 'Pending'";
    $page_title = "Pending Approvals Panel";
    $alert_theme = "border-danger text-danger bg-danger-subtle";
} elseif ($filter_type === 'Completed') {
    $sql_condition = "AND status = 'Completed'";
    $page_title = "Completed Consultations Registry";
    $alert_theme = "border-success text-success bg-success-subtle";
} else {
    $sql_condition = "";
    $page_title = "Total Consultations Dossier";
    $alert_theme = "border-primary text-primary bg-primary-subtle";
}

$query_string = "SELECT * FROM appointments WHERE doctor_id = $doctor_id $sql_condition ORDER BY id DESC";
$dataset = mysqli_query($conn, $query_string);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Al Shifa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; padding: 3rem 1.5rem; }
        .filter-container { background: white; border-radius: 24px; padding: 2.5rem; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .table thead th { background: #f8fafc; border: none; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 1.2rem 1rem; }
        .table tbody td { padding: 1.2rem 1rem; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<div class="container">
    <div class="mb-4">
        <a href="doctor-dashboard.php" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm mb-3">
            <i class="bi bi-arrow-left me-2"></i> Back to Primary Dashboard
        </a>
    </div>

    <div class="filter-container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3 class="fw-bold text-dark mb-1"><?php echo $page_title; ?></h3>
                <p class="text-muted small mb-0">Isolating system appointments records</p>
            </div>
            <span class="badge border rounded-pill px-3 py-2 fw-bold <?php echo $alert_theme; ?>">
                Filtered View: <?php echo $filter_type; ?> (Count: <?php echo mysqli_num_rows($dataset); ?>)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Email Contact</th>
                        <th>Scheduled Date</th>
                        <th>Target Problem</th>
                        <th>Meeting Link</th>
                        <th>Status</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($dataset) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($dataset)): ?>
                        <tr>
                            <td><span class="text-muted small fw-bold">#AS-<?php echo $row['id']; ?></span></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><span class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></span></td>
                            <td><span class="text-dark small fw-semibold"><?php echo date('d M Y', strtotime($row['app_date'])); ?></span></td>
                            <td><div class="text-truncate small" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['problem']); ?>"><?php echo htmlspecialchars($row['problem']); ?></div></td>
                            <td>
                                <?php if(!empty($row['meeting_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['meeting_link']); ?>" target="_blank" class="btn btn-sm btn-light border text-primary rounded-pill px-3 small">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Launch Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic">Not uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-1.5 <?php echo ($row['status'] == 'Completed') ? 'bg-success-subtle text-success' : (($row['status'] == 'Pending') ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning'); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold <?php echo ($row['fee_status'] == 'Paid') ? 'text-success' : 'text-danger'; ?>">
                                    <i class="bi <?php echo ($row['fee_status'] == 'Paid') ? 'bi-cash-stack' : 'bi-x-circle'; ?> me-1"></i><?php echo htmlspecialchars($row['fee_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                No matching records discovered inside this isolated dynamic state.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>