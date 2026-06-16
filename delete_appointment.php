<?php
session_start();
// Database connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $appointment_id = intval($_GET['id']);
    
    // Sirf patient dashboard se record ko soft-delete (hide) karega
    $sql = "UPDATE appointments SET patient_hidden = 1 WHERE id = $appointment_id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: patient-dashboard.php?msg=success");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: patient-dashboard.php?msg=error");
    exit();
}
?>