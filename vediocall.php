<?php
session_start();

// Railway dynamic production credentials connection layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$app_id = isset($_GET['app_id']) ? mysqli_real_escape_string($conn, $_GET['app_id']) : 'AL-992'; 

if (isset($_GET['check_status'])) {
    $query = "SELECT video_link FROM appointments WHERE appointment_id = '$app_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    header('Content-Type: application/json');
    echo json_encode(['video_link' => $row['video_link']]);
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Room - Al Shifa TeleHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --google-blue: #1a73e8; --primary: #4042a1; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8f9fa; height: 100vh; display: flex; overflow: hidden; margin: 0; }

        /* Sidebar */
        .sidebar { width: 280px; border-right: 1px solid #dadce0; background: white; display: flex; flex-direction: column; }
        .nav-item { padding: 12px 24px; margin: 4px 15px; border-radius: 12px; cursor: pointer; color: #5f6368; display: flex; align-items: center; gap: 15px; transition: 0.3s; text-decoration: none; }
        .nav-item.active { background: #e8f0fe; color: var(--google-blue); font-weight: 600; }

        .main-content { flex: 1; display: flex; flex-direction: column; }
        header { padding: 15px 30px; background: white; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }

        /* Waiting Room UI */
        .waiting-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .waiting-card { max-width: 500px; width: 100%; text-align: center; background: white; padding: 40px; border-radius: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        
        .pulse-loader {
            width: 80px; height: 80px; background: rgba(26, 115, 232, 0.1);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 25px; position: relative;
        }
        .pulse-loader i { font-size: 30px; color: var(--google-blue); z-index: 2; }
        .pulse-loader::after {
            content: ""; position: absolute; width: 100%; height: 100%;
            border: 2px solid var(--google-blue); border-radius: 50%;
            animation: pulse-ring 1.5s infinite;
        }

        @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 0.8; } 100% { transform: scale(1.5); opacity: 0; } }

        .btn-join-live {
            background: #28a745; color: white; border: none; padding: 15px 40px;
            border-radius: 50px; font-weight: 700; font-size: 1.1rem;
            display: none; width: 100%; margin-top: 20px; text-decoration: none;
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            animation: bounce 2s infinite;
        }

        @keyframes bounce { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-10px);} }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="p-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-heart-pulse text-danger fs-4"></i>
            <span class="fw-bold fs-5" style="color: var(--primary)">Al Shifa</span>
        </div>
        <a href="#" class="nav-item active"><i class="fa-solid fa-video"></i> Waiting Room</a>
        <a href="patient-dashboard.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i> My Appointments</a>
        <div class="mt-auto p-4">
            <a href="index.php" class="btn btn-light w-100 border-0 rounded-pill"><i class="fa-solid fa-house me-2"></i>Exit Room</a>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div id="clock" class="text-muted fw-medium">--:--</div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-success border border-success px-3">Live System</span>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px">P</div>
            </div>
        </header>

        <div class="waiting-container">
            <div class="waiting-card">
                <div class="pulse-loader"><i class="fa-solid fa-video"></i></div>
                <h3 class="fw-bold">Virtual Waiting Room</h3>
                <p id="statusText" class="text-muted">Assalam-o-Alaikum! Please stay on this page. Doctor will start the meeting shortly.</p>
                
                <a href="#" id="dynamicJoinBtn" target="_blank" class="btn btn-join-live">
                    <i class="fa-solid fa-door-open me-2"></i> Join Meeting Now
                </a>
                
                <div class="mt-4 p-3 bg-light rounded-3 small text-start">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i> 
                    <b>ID: <?php echo $app_id; ?></b> - Hum database check kar rahe hain...
                </div>
            </div>
        </div>
    </div>

<script>
    // Clock function
    function updateClock() {
        document.getElementById('clock').innerText = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- Backend Check Logic (XAMPP PHP) ---
    function checkDoctorLink() {
        // Apne hi page par AJAX request bhejna
        fetch(`vedio.php?app_id=<?php echo $app_id; ?>&check_status=1`)
            .then(response => response.json())
            .then(data => {
                const btn = document.getElementById('dynamicJoinBtn');
                const statusText = document.getElementById('statusText');

                if (data.meeting_link && data.meeting_link !== "") {
                    btn.style.display = 'block';
                    btn.href = data.meeting_link;
                    statusText.innerHTML = "<strong>Doctor is ready!</strong> Click the button below to join the call.";
                } else {
                    btn.style.display = 'none';
                }
            })
            .catch(err => console.error("Error fetching link:", err));
    }

    // Har 5 second baad check karega
    setInterval(checkDoctorLink, 5000);
    checkDoctorLink(); // Pehli baar foran check kare
</script>
</body>
</html>