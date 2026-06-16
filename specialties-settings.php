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

// --- ACTION 1: ADD NEW SPECIALTY ---
if (isset($_POST['add_specialty'])) {
    $spec_name = mysqli_real_escape_string($conn, trim($_POST['specialty_name']));
    
    if (!empty($spec_name)) {
        // Safe validation check for duplicates
        $check = @mysqli_query($conn, "SELECT id FROM specialties WHERE name = '$spec_name'");
        if (!$check) {
            $message = "<div class='alert alert-danger'>Error: 'specialties' table database mein maujood nahi hai. Pehle table create karein.</div>";
        } else if (mysqli_num_rows($check) > 0) {
            $message = "<div class='alert alert-danger'>Error: Specialty category already exists.</div>";
        } else {
            if (mysqli_query($conn, "INSERT INTO specialties (name) VALUES ('$spec_name')")) {
                $message = "<div class='alert alert-success'>Nayi department category (<strong>$spec_name</strong>) successfully link ho chuki hai!</div>";
            }
        }
    }
}

// --- ACTION 2: DELETE SPECIALTY ---
if (isset($_GET['delete_spec_id'])) {
    $del_id = intval($_GET['delete_spec_id']);
    if (@mysqli_query($conn, "DELETE FROM specialties WHERE id = $del_id")) {
        $message = "<div class='alert alert-warning'>Specialty node detached successfully.</div>";
    }
}

// Fetch All Specialties safely without throwing fatal exceptions
$specs_result = @mysqli_query($conn, "SELECT * FROM specialties ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Settings & Specialization Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; }
        .sidebar { background: #1e293b; min-height: 100vh; color: #fff; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: #fff; }
        .config-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
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
                <h2 class="fw-bold m-0">Global Specialties Configurations</h2>
                <p class="text-muted small">Construct or append target clinical specialization streams dynamic vectors.</p>
            </div>

            <?php echo $message; ?>

            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card config-card p-4 bg-white">
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Add Clinic Department</h5>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Specialty / Department Title</label>
                                <input type="text" name="specialty_name" class="form-control" placeholder="e.g. Neurology, Cardiology" required>
                                <div class="form-text text-muted" style="font-size:11px;">
                                    This string parameter transforms drop-down behaviors across the booking UI layer.
                                </div>
                            </div>
                            <button type="submit" name="add_specialty" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
                                CREATE DOMAIN CATEGORY <i class="bi bi-plus-lg ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card config-card p-3 bg-white">
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-tags text-success me-2"></i>System Classification Tags</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tag Index ID</th>
                                        <th>Medical Specialty Branch</th>
                                        <th class="text-center">Action Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($specs_result && mysqli_num_rows($specs_result) > 0) {
                                        while($spec = mysqli_fetch_assoc($specs_result)):
                                    ?>
                                    <tr>
                                        <td><strong>#SPEC-0<?php echo $spec['id']; ?></strong></td>
                                        <td><span class="badge bg-dark-subtle text-dark fw-bold px-3 py-2 fs-6"><?php echo htmlspecialchars($spec['name']); ?></span></td>
                                        <td class="text-center">
                                            <a href="specialties-settings.php?delete_spec_id=<?php echo $spec['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to clear this specialization branch tag?');">
                                                <i class="bi bi-trash-fill"></i> Drop Node
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center py-4 text-muted'><i class='bi bi-exclamation-triangle text-warning me-1'></i> No specialty tags setup yet. Please ensure the 'specialties' table is created in your phpMyAdmin database.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>