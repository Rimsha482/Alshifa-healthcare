<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Railway dynamic production credentials connection layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

$app_id = isset($_GET['app_id']) ? intval($_GET['app_id']) : 0;
$message = "";

// 1. Get Appointment details and fetch assigned Doctor's accounts dynamically
$query = "SELECT a.*, u.name as doctor_name, u.phone as doctor_phone, u.email as doctor_email 
          FROM appointments a 
          LEFT JOIN users u ON a.doctor = u.name 
          WHERE a.id = '$app_id'";
          
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<div class='container mt-5 alert alert-danger'>Invalid Appointment Reference Token ID.</div>");
}

// Fallback accounts agar kisi doctor ka dynamic phone database mein na ho
$doctor_payment_no = (!empty($data['doctor_phone'])) ? $data['doctor_phone'] : "0300-1234567";
$doctor_name_title = (!empty($data['doctor_name'])) ? $data['doctor_name'] : "Al Shifa Central Vault";

// 2. Process Submission Layer
if (isset($_POST['confirm_payment'])) {
    $tid = mysqli_real_escape_string($conn, trim($_POST['tid']));
    
    // File upload settings
    $target_dir = "uploads/screenshots/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Automatically directory create karega agar local server par na ho
    }
    
    $file_name = time() . '_' . basename($_FILES["screenshot"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image is a actual image
    $check = getimagesize($_FILES["screenshot"]["tmp_name"]);
    if($check === false) {
        $message = "<div class='alert alert-danger'>Uploaded file is not a valid transaction snapshot image.</div>";
    }
    // Limit file size (Max 5MB)
    elseif ($_FILES["screenshot"]["size"] > 5000000) {
        $message = "<div class='alert alert-danger'>Snapshot size exceeds maximum allowable limit of 5MB.</div>";
    }
    // Allow certain file formats
    elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $message = "<div class='alert alert-danger'>Only JPG, JPEG, & PNG verification graphics are allowed.</div>";
    }
    else {
        // Move file to deployment directory and update ledger parameters
        if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_file)) {
            
            // Database update matrix (TID, Screenshot path and updated statuses)
            $screenshot_path = mysqli_real_escape_string($conn, $target_file);
            $update_query = "UPDATE appointments 
                             SET transaction_id = '$tid', 
                                 payment_screenshot = '$screenshot_path', 
                                 fee_status = 'Pending Verification' 
                             WHERE id = '$app_id'";
                             
            if (mysqli_query($conn, $update_query)) {
                echo "<script>
                        alert('Payment proof logged successfully! Our accounts desk will verify your remittance snapshot shortly.');
                        window.location.href='patient-dashboard.php';
                      </script>";
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Failed to save file snapshot to live cloud core stack. Check file permissions.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Gateway Checkouts | Al Shifa Medical Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7fc; }
        .payment-card { border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .doc-badge { background: #eff2ff; border-left: 4px solid #4042a1; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card payment-card border-0 p-4 bg-white">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check text-success fs-1"></i>
                    <h4 class="fw-bold text-dark mt-2">Verified Checkout Portal</h4>
                </div>

                <?php echo $message; ?>

                <div class="alert alert-primary text-center py-3 mb-4 rounded-3">
                    <span class="d-block small text-uppercase tracking-wider opacity-75">Payable Consultation Fee</span>
                    <h2 class="fw-bold m-0 text-primary">Rs. <?php echo number_format($data['fee_amount'] ?? $data['fee_status']); ?>/-</h2>
                </div>

                <div class="doc-badge p-3 mb-4 rounded-3">
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-person-heart me-2 text-primary"></i>Assigned Medical Practitioner</h6>
                    <p class="mb-0 small text-muted">Fees will route directly to the consultant account below:</p>
                    <div class="mt-3 pt-2 border-top border-secondary-subtle small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Account Title:</span>
                            <strong class="text-dark"><?php echo htmlspecialchars($doctor_name_title); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Merchant Network:</span>
                            <span class="badge bg-warning text-dark fw-bold">JazzCash / EasyPaisa</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Account / Mobile Number:</span>
                            <strong class="text-danger fs-6"><?php echo htmlspecialchars($doctor_payment_no); ?></strong>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-send-check me-2 text-success"></i>Upload Remittance Proof</h6>
                    
                   

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Upload Transaction Confirmation Screenshot</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/*" required>
                        <div class="form-text text-muted small" style="font-size:11px; line-height:1.3;">
                            <i class="bi bi-info-circle me-1 text-primary"></i> 
                            Please take a crisp full screenshot of the app success page showing standard dynamic data points: Date, Receiver Name & Transferred Value. Max 5MB (.jpg, .jpeg, .png).
                        </div>
                    </div>

                    <button type="submit" name="confirm_payment" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                        SUBMIT TRANSACTION ATTESTATION <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small" style="font-size: 11px;"><i class="bi bi-lock-fill me-1"></i> End-to-End SSL Encrypted Processing Pipeline</p>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>