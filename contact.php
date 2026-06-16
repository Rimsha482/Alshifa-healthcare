<?php
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

$show_success = false;
$redirect_url = "";
$error_msg = "";

if (isset($_POST['book_now'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $dept = mysqli_real_escape_string($conn, $_POST['dept']);
    $problem = mysqli_real_escape_string($conn, $_POST['problem']);

    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $error_msg = "Patient Name mein sirf alphabets allowed hain!";
    } else {
        // Doctor ID, Name aur Fee extraction from the select value
        $doctor_data = mysqli_real_escape_string($conn, $_POST['doctor']);
        $doctor_parts = explode('|', $doctor_data);
        
        $doctor_id = intval($doctor_parts[0]); 
        $doctor_name = $doctor_parts[1]; 
        $fee = intval($doctor_parts[2]); 

        $sql = "INSERT INTO appointments (name, email, phone, app_date, department, doctor, doctor_id, fee_amount, payment_status, status, problem) 
                VALUES ('$name', '$email', '$phone', '$date', '$dept', '$doctor_name', '$doctor_id', '$fee', 'Unpaid', 'Pending', '$problem')";

        if (mysqli_query($conn, $sql)) {
            $last_id = mysqli_insert_id($conn);
            $show_success = true;
            $redirect_url = "payment.php?app_id=$last_id"; 
        } else {
            $error_msg = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | Al Shifa HealthCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary-color: #4042a1; }
        body { background-color: #f4f7fe; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: var(--primary-color); }
        .contact-card { 
            border-radius: 20px; 
            border: none; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            background: #fff;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #e0e0e0;
            background: #fdfdfd;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(64, 66, 161, 0.1);
            border-color: var(--primary-color);
        }
        .btn-primary { 
            background: var(--primary-color); 
            border: none; 
            border-radius: 12px;
            padding: 15px;
            font-weight: 700;
            transition: 0.3s; 
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(64, 66, 161, 0.2); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3">
    <div class="container text-center">
        <a class="navbar-brand fw-bold mx-auto" href="index.php">
            <i class="bi bi-heart-pulse-fill me-2"></i>AL SHIFA MEDICAL PORTAL
        </a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 animate__animated animate__fadeInUp">
            
            <?php if ($show_success): ?>
            <div class="alert alert-success text-center shadow border-0 p-4 mb-4">
                <div class="spinner-border text-success mb-3" role="status"></div>
                <h4 class="fw-bold">Booking Request Saved!</h4>
                <p class="mb-0 text-muted">Aapko Payment page par bheja ja raha hai...</p>
            </div>
            <script>setTimeout(function() { window.location.href = "<?php echo $redirect_url; ?>"; }, 2500);</script>
            <?php endif; ?>

            <?php if ($error_msg != ""): ?>
            <div class="alert alert-warning border-0 shadow-sm animate__animated animate__shakeX">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
            </div>
            <?php endif; ?>

            <div class="card contact-card p-4 p-md-5">
                <div class="mb-4 text-center">
                    <h2 class="fw-bold text-dark">Book New Appointment</h2>
                    <p class="text-muted">Enter patient details to schedule a visit</p>
                </div>

                <form action="" method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Patient Full Name</label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="Alphabets Only (e.g. Ali Raza)" 
                                   pattern="[A-Za-z ]+" 
                                   title="Numbers (123) ya special characters allowed nahi hain."
                                   required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contact Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="03xxxxxxxxx" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="patient@example.com" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Preferred Date</label>
                            <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Specialist Department</label>
                            <select name="dept" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php
                                // FIXED QUERY: `status=1` ko hata kar query ko open kiya hai taaki departments load ho skein
                                $dept_res = mysqli_query($conn, "SELECT DISTINCT specialization FROM users WHERE role='doctor' AND specialization IS NOT NULL AND specialization != ''");
                                if($dept_res && mysqli_num_rows($dept_res) > 0) {
                                    while($d = mysqli_fetch_assoc($dept_res)){
                                        echo "<option value='{$d['specialization']}'>{$d['specialization']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Departments Found</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Choose Specialist Doctor</label>
                            <select name="doctor" class="form-select" required>
                                <option value="">Select Doctor (Fee Details)</option>
                                <?php
                                // FIXED QUERY: `status=1` ka issue bypass kiya hai taaki tamaam registered doctors options mein show hon
                                $dr_res = mysqli_query($conn, "SELECT id, name, specialization, fee FROM users WHERE role='doctor'");
                                if($dr_res && mysqli_num_rows($dr_res) > 0) {
                                    while($dr = mysqli_fetch_assoc($dr_res)){
                                        $combined_val = $dr['id'] . "|" . $dr['name'] . "|" . $dr['fee'];
                                        echo "<option value='$combined_val'> {$dr['name']} - {$dr['specialization']} (Rs. {$dr['fee']})</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Doctors Available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Current Health Issue / Symptoms</label>
                            <textarea name="problem" class="form-control" rows="3" placeholder="Briefly describe the medical condition..."></textarea>
                        </div>
                    </div>

                    <button type="submit" name="book_now" class="btn btn-primary w-100 mt-5 shadow">
                        CONFIRM & PROCEED TO PAYMENT <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>