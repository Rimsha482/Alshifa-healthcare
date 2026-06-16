<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Railway/XAMPP dynamic database connection layer
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

// Placeholder image array to assign beautiful visuals dynamically to new departments
$stock_images = [
    'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?q=80&w=500',
    'https://images.pexels.com/photos/2280571/pexels-photo-2280571.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?q=80&w=500',
    'https://images.unsplash.com/photo-1584515933487-779824d29309?q=80&w=500',
    'https://images.unsplash.com/photo-1576091160550-2173dba999ef?q=80&w=500'
];

$services = [];

if ($conn) {
    // Database se dynamic data tags fetch karein
    $db_specs = @mysqli_query($conn, "SELECT name FROM specialties ORDER BY id ASC");
    if ($db_specs && mysqli_num_rows($db_specs) > 0) {
        $idx = 0;
        while ($row = mysqli_fetch_assoc($db_specs)) {
            $title = $row['name'];
            // Assign image from stock list based on index loop rotation
            $assigned_img = $stock_images[$idx % count($stock_images)];
            
            $services[] = [
                'title' => $title,
                'img'   => $assigned_img,
                'desc'  => "Advanced clinical services and care configurations specialized for " . htmlspecialchars($title) . " departments."
            ];
            $idx++;
        }
    }
}

// Fallback System: Agar database connectivity drop ho ya table khali ho, to ye automatically run karega
if (empty($services)) {
    $services = [
        ['title' => 'General Checkup', 'img' => $stock_images[0], 'desc' => 'Comprehensive health screenings for you and your family.'],
        ['title' => 'Cardiology', 'img' => $stock_images[1], 'desc' => 'Expert heart specialists with advanced cardiac diagnostic tools.'],
        ['title' => 'Laboratory', 'img' => $stock_images[2], 'desc' => 'Fast and accurate medical testing with digital reports.'],
        ['title' => 'Pediatrics', 'img' => $stock_images[3], 'desc' => 'Specialized medical care for your little ones in a safe environment.'],
        ['title' => 'Dermatology', 'img' => $stock_images[4], 'desc' => 'Advanced skin treatments and aesthetic care by experts.'],
        ['title' => 'Physiotherapy', 'img' => $stock_images[5], 'desc' => 'Recover faster with our personalized rehab programs.']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services - Al Shifa HealthCare</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <style>
    :root {
      --primary: #4042a1;
      --secondary: #5f61d8;
      --accent: #00d2ff;
      --light-bg: #f8fafd;
      --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: var(--light-bg);
      color: #2d3436;
      overflow-x: hidden;
    }

    /* --- Navbar Glassmorphism --- */
    .navbar {
      transition: all 0.5s ease;
      background: rgba(64, 66, 161, 0.9) !important;
      backdrop-filter: blur(15px);
      padding: 15px 0;
    }

    .nav-link {
      color: rgba(255,255,255,0.8) !important;
      font-weight: 600;
      transition: var(--transition);
    }

    .nav-link:hover, .nav-link.active {
      color: #fff !important;
      transform: translateY(-2px);
    }

    /* --- Hero Section --- */
    .hero-header {
      background: linear-gradient(rgba(64, 66, 161, 0.8), rgba(95, 97, 216, 0.8)), 
                  url('https://images.unsplash.com/photo-1512678080530-7760d81faba6?auto=format&fit=crop&w=1920&q=80');
      background-attachment: fixed;
      background-size: cover;
      background-position: center;
      padding: 150px 0 120px;
      color: white;
      text-align: center;
      clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
      margin-bottom: 50px;
    }

    /* --- Service Card --- */
    .service-card {
      background: white;
      border-radius: 24px;
      overflow: hidden;
      transition: var(--transition);
      border: none;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      height: 100%;
    }

    .service-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 20px 40px rgba(64, 66, 161, 0.15);
    }

    .img-wrapper {
      position: relative;
      overflow: hidden;
      height: 240px;
    }

    .service-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: 0.5s;
      filter: saturate(1.1) hue-rotate(-5deg);
    }

    .service-card:hover .service-img {
      transform: scale(1.1);
      filter: brightness(0.8) saturate(1.3);
    }

    .img-wrapper::after {
      content: '';
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      background: linear-gradient(to bottom, transparent, rgba(64, 66, 161, 0.2));
      pointer-events: none;
    }

    .card-body {
      padding: 30px 20px;
      text-align: center;
    }

    .card-title {
      color: var(--primary);
      font-weight: 800;
      margin-bottom: 15px;
    }

    .section-subtitle {
        color: var(--accent);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        font-size: 14px;
    }

    .btn-book {
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 10px 30px;
      font-weight: 700;
      transition: var(--transition);
      box-shadow: 0 5px 15px rgba(64, 66, 161, 0.2);
    }

    .btn-book:hover {
      background: var(--secondary);
      color: white;
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(64, 66, 161, 0.3);
    }

    .pulse-icon {
      animation: pulse 2s infinite;
      color: var(--accent);
    }

    @keyframes pulse {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.2); opacity: 0.7; }
      100% { transform: scale(1); opacity: 1; }
    }

    footer {
      background: #1a1b4b;
      color: white;
      padding: 60px 0;
      border-top-left-radius: 50px;
      border-top-right-radius: 50px;
      margin-top: 80px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shield-plus me-2"></i>AL SHIFA</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link active" href="services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
        <li class="nav-item"><a class="nav-link" href="vediocall.php"><i class="bi bi-camera-video-fill pulse-icon fs-5"></i></a></li> 
         <li class="nav-item dropdown ms-lg-3">
                <a class="btn btn-info rounded-pill px-4 btn-sm fw-bold dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    Account
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate__animated animate__fadeIn">
                    <?php 
                    $role = $_SESSION['user_role'] ?? '';
                    if($role == 'admin'): ?>
                        <li><a class="dropdown-item fw-bold text-primary" href="admin-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                    <?php elseif($role == 'doctor'): ?>
                        <li><a class="dropdown-item" href="doctor-dashboard.php"><i class="bi bi-calendar-check me-2"></i>Doctor Dashboard</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="patient-dashboard.php"><i class="bi bi-person-badge me-2"></i>Patient Dashboard</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
      </ul>
    </div>
  </div>
</nav>

<header class="hero-header">
  <div class="container" data-aos="zoom-out">
    <span class="badge bg-info mb-3 px-3 py-2 text-uppercase fw-bold shadow-sm">Premium Care</span>
    <h1 class="display-3 fw-bold">Specialized Services</h1>
    <p class="lead opacity-75">Cutting-edge medical technology meet compassionate care.</p>
  </div>
</header>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold display-6" style="color: var(--primary);">Clinical Excellence</h2>
        <div style="width: 60px; height: 5px; background: var(--accent); margin: 10px auto; border-radius: 10px;"></div>
    </div>

    <div class="row g-4">
      <?php foreach ($services as $i => $s): ?>
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $i * 100; ?>">
        <div class="service-card">
          <div class="img-wrapper">
            <img src="<?php echo $s['img']; ?>" class="service-img" alt="<?php echo htmlspecialchars($s['title']); ?>">
          </div>
          <div class="card-body">
            <span class="section-subtitle">Al Shifa Care</span>
            <h4 class="card-title"><?php echo htmlspecialchars($s['title']); ?></h4>
            <p class="text-muted small mb-4"><?php echo htmlspecialchars($s['desc']); ?></p>
            <a href="contact.php?service=<?php echo urlencode($s['title']); ?>" class="btn btn-book">Book Now</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<footer>
  <div class="container text-center">
    <h2 class="fw-bold mb-3"><i class="bi bi-shield-plus me-2"></i>AL SHIFA</h2>
    <p class="opacity-75 mb-4">Ada Sikandri 49/M, Lodhran, Punjab, Pakistan<br>
    Contact: +92 300 0000000 | info@alshifamedicalcentre.com</p>
    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="#" class="btn btn-outline-light rounded-circle p-2" style="width:40px; height:40px;"><i class="bi bi-facebook"></i></a>
        <a href="#" class="btn btn-outline-light rounded-circle p-2" style="width:40px; height:40px;"><i class="bi bi-whatsapp"></i></a>
        <a href="#" class="btn btn-outline-light rounded-circle p-2" style="width:40px; height:40px;"><i class="bi bi-envelope"></i></a>
    </div>
    <hr class="opacity-25 mb-4">
    <p class="small opacity-50 mb-0">&copy; 2026 Al Shifa Healthcare. Designed for Clinical Excellence.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>
</body>
</html>