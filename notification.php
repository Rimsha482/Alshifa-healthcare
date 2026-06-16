<?php
session_start();

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// 1. Fetch General Notifications from 'notifications' table
$notif_query = "SELECT * FROM notifications ORDER BY timestamp DESC";
$notif_result = mysqli_query($conn, $notif_query);

// 2. Fetch Meeting Link Updates from 'contacts' table (Specific for this user)
$link_query = "SELECT doctor, videoLink, date FROM contacts WHERE email = '$user_email' AND videoLink IS NOT NULL AND videoLink != '' ORDER BY id DESC";
$link_result = mysqli_query($conn, $link_query);

$total_count = mysqli_num_rows($notif_result) + mysqli_num_rows($link_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Al Shifa Medical Centre</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --google-blue: #1a73e8; --bg-light: #f8f9fa; }
        body { font-family: 'Segoe UI', Roboto, Arial; background-color: var(--bg-light); }
        .navbar { background-color: #ffffff !important; border-bottom: 1px solid #dadce0; }
        .navbar-brand { color: var(--google-blue) !important; font-weight: bold; }
        
        .notification-card {
            border: none;
            border-left: 5px solid var(--google-blue);
            transition: 0.3s;
            border-radius: 8px;
            background: white;
        }
        .card-link-update { border-left-color: #34a853; } /* Green for meeting links */
        
        .notification-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .icon-box {
            width: 45px; height: 45px;
            background: #e8f0fe;
            color: var(--google-blue);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .empty-state { padding: 60px 20px; text-align: center; color: #5f6368; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-house-medical me-2"></i>Al Shifa HealthCare
        </a>
        <div class="ms-auto">
            <a href="patient-dashboard.php" class="btn btn-sm btn-outline-primary rounded-pill">My Dashboard</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="fw-bold mb-0">📢 Notifications</h2>
                <span class="badge bg-primary rounded-pill"><?php echo $total_count; ?></span>
            </div>
            
            <div id="notificationList">
                
                <!-- 1. Meeting Link Notifications (Fetched from Contacts) -->
                <?php while($link = mysqli_fetch_assoc($link_result)): ?>
                    <div class="card notification-card card-link-update mb-3 shadow-sm">
                        <div class="card-body d-flex gap-3">
                            <div class="icon-box flex-shrink-0" style="background: #e6f4ea; color: #1e8e3e;">
                                <i class="fa-solid fa-video"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Meeting Link Ready!</h6>
                                <p class="text-secondary small mb-2">
                                    <?php echo htmlspecialchars($link['doctor']); ?> has sent your consultation link for the appointment on <?php echo $link['date']; ?>.
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?php echo $link['videoLink']; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3" style="font-size: 11px;">
                                        Join Meeting Now
                                    </a>
                                    <span class="text-muted" style="font-size: 11px;">Live Update</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- 2. General System Notifications -->
                <?php if(mysqli_num_rows($notif_result) > 0): ?>
                    <?php while($notif = mysqli_fetch_assoc($notif_result)): ?>
                        <div class="card notification-card mb-3 shadow-sm">
                            <div class="card-body d-flex gap-3">
                                <div class="icon-box flex-shrink-0">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                    <p class="text-secondary small mb-2"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size: 11px;">
                                            <i class="fa-regular fa-clock me-1"></i><?php echo date('d M, h:i A', strtotime($notif['timestamp'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <!-- Empty State -->
                <?php if($total_count == 0): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-bell-slash fa-4x mb-3" style="color: #dadce0;"></i>
                        <h4>No notifications yet</h4>
                        <p>We'll notify you when your doctor sends a link or there's an update.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>