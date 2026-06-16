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

// Counts pull karne ke liye safe dynamic defaults
$total_specialties = 6; // Default safe fallback

if ($conn) {
    // Database se dynamic specialties ka count check karein
    $count_query = @mysqli_query($conn, "SELECT COUNT(*) as total FROM specialties");
    if ($count_query) {
        $count_data = mysqli_fetch_assoc($count_query);
        if ($count_data['total'] > 0) {
            $total_specialties = $count_data['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us | Al Shifa HealthCare</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
        --primary: #4042a1;
        --secondary: #5f61d8;
        --accent: #00d2ff;
        --light-bg: #f8fafd;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--light-bg); color: #2d3436; }

    /* Navbar Glassmorphism */
    .navbar { 
        background: rgba(64, 66, 161, 0.9) !important; 
        backdrop-filter: blur(15px);
        padding: 15px 0;
    }

    /* Hero Section with Parallax */
    .hero-header { 
        background: linear-gradient(rgba(64, 66, 161, 0.8), rgba(95, 97, 216, 0.8)), 
                    url('https://images.unsplash.com/photo-1512678080530-7760d81faba6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        color: white; padding: 120px 20px; text-align: center;
        clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
    }

    /* Section Title */
    .section-title { 
        color: var(--primary); font-weight: 800; 
        position: relative; display: inline-block; padding-bottom: 10px;
    }
    .section-title::after { 
        content: ''; position: absolute; left: 0; bottom: 0; 
        width: 60px; height: 5px; background: var(--accent); border-radius: 10px;
    }

    /* Info Boxes */
    .info-box {
        background: white; border-radius: 20px; padding: 25px;
        transition: 0.3s; border: 1px solid rgba(0,0,0,0.05);
        height: 100%;
    }
    .info-box:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(64, 66, 161, 0.1); }
    .info-icon { width: 50px; height: 50px; background: #eef0ff; color: var(--primary); 
                 display: flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 15px; }

    /* Doctor Card Redesign */
    .doctor-card {
        background: white; border-radius: 24px; overflow: hidden;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    .doctor-card:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .doctor-img-wrapper { position: relative; overflow: hidden; height: 320px; }
    .doctor-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .doctor-card:hover .doctor-img { transform: scale(1.1); filter: brightness(0.8); }
    
    .doctor-info { padding: 20px; text-align: center; }

    footer { background: #1a1b4b; color: white; padding: 60px 0; border-top-left-radius: 50px; border-top-right-radius: 50px; }

    @media (max-width: 768px) {
        .hero-header { padding: 80px 10px; clip-path: none; }
        .hero-header h1 { font-size: 32px; }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shield-plus me-2"></i>AL SHIFA</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
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
                        <li><a class="dropdown-item" href="doctor-dashboard.php"><i class="bi bi-calendar-check me-2"></i>Appointments</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="patient-dashboard.php"><i class="bi bi-person-badge me-2"></i>Patient Portal</a></li>
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
    <span class="badge bg-info mb-3 px-3 py-2 text-uppercase fw-bold shadow-sm">Established Since 2010</span>
    <h1 class="display-3 fw-bold">Our Commitment to Health</h1>
    <p class="lead opacity-75">Providing world-class medical excellence to the heart of Lodhran.</p>
  </div>
</header>

<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="position-relative">
            <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-4 shadow-lg" alt="Al Shifa Clinic Interior">
            <div class="position-absolute top-0 end-0 bg-white p-3 rounded-4 shadow m-3 d-none d-md-block" data-aos="fade-left" data-aos-delay="500">
                <h2 class="fw-bold text-primary mb-0">14+</h2>
                <small class="text-muted">Years of Excellence</small>
            </div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <h2 class="section-title mb-4">A Beacon of Hope in Lodhran</h2>
        <p class="lead text-primary fw-bold">Al Shifa Medical Centre is not just a clinic; it's a sanctuary for healing.</p>
        <p class="text-muted">Humara maqsad mareezon ko sasti aur behtareen medical sahuliyat faraham karna hai. Ada Sikandri Lodhran mein waqay ye centre modern technology aur pur-khuloos staff ke saath aapki sehat ka 24/7 khayal rakhta hai.</p>
        
        <div class="row g-3 mt-2">
            <div class="col-sm-6">
                <div class="info-box">
                    <div class="info-icon"><i class="bi bi-patch-check-fill fs-4"></i></div>
                    <h6 class="fw-bold">ISO Certified</h6>
                    <p class="small text-muted mb-0">Maintaining international safety standards in healthcare.</p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="info-box">
                    <div class="info-icon"><i class="bi bi-people-fill fs-4"></i></div>
                    <h6 class="fw-bold">Active Branches</h6>
                    <p class="small text-muted mb-0">Over <?php echo $total_specialties; ?>+ dynamic operational specialty tags.</p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" style="background-color: #f0f3ff;">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold display-6" style="color: var(--primary);">Meet Our Specialists</h2>
        <p class="text-muted">Highly qualified doctors dedicated to your wellness.</p>
    </div>
    <div class="row g-4">
      
      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="doctor-card">
          <div class="doctor-img-wrapper">
              <img src="https://images.pexels.com/photos/4173239/pexels-photo-4173239.jpeg?auto=compress&cs=tinysrgb&w=800" class="doctor-img" alt="Cardiologist">
          </div>
          <div class="doctor-info">
              <h5 class="fw-bold mb-1">Dr. Ahmed Khan</h5>
              <span class="badge bg-primary-subtle text-primary mb-3">Senior Cardiologist</span>
              <a href="contact.php?dr=Dr. Ahmed Khan" class="btn btn-sm btn-primary w-100 rounded-pill shadow-sm">Book Appointment</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="doctor-card">
          <div class="doctor-img-wrapper">
              <img src="https://images.pexels.com/photos/4173251/pexels-photo-4173251.jpeg?auto=compress&cs=tinysrgb&w=800" class="doctor-img" alt="General Physician">
          </div>
          <div class="doctor-info">
              <h5 class="fw-bold mb-1">Dr. Sana Malik</h5>
              <span class="badge bg-success-subtle text-success mb-3">General Physician</span>
              <a href="contact.php?dr=Dr. Sana Malik" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Book Appointment</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="doctor-card">
          <div class="doctor-img-wrapper">
            <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="doctor-img" alt="Dr. Ali Raza Pediatrician">
          </div>
          <div class="doctor-info">
              <h5 class="fw-bold mb-1">Dr. Ali Raza</h5>
              <span class="badge bg-info-subtle text-info mb-3">Pediatrician</span>
              <a href="contact.php?dr=Dr. Ali Raza" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Book Appointment</a>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
        <div class="doctor-card">
          <div class="doctor-img-wrapper">
              <img src="https://images.pexels.com/photos/5215024/pexels-photo-5215024.jpeg?auto=compress&cs=tinysrgb&w=800" class="doctor-img" alt="Dermatologist">
          </div>
          <div class="doctor-info">
              <h5 class="fw-bold mb-1">Dr. Nadia Iqbal</h5>
              <span class="badge bg-warning-subtle text-warning mb-3">Dermatologist</span>
              <a href="contact.php?dr=Dr. Nadia Iqbal" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Book Appointment</a>
          </div>
        </div>
      </div>

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
        <a href="#" class="btn btn-outline-light rounded-circle p-2" style="width:40px; height:40px;"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="btn btn-outline-light rounded-circle p-2" style="width:40px; height:40px;"><i class="bi bi-instagram"></i></a>
    </div>
    <hr class="opacity-25 mb-4">
    <p class="small opacity-50 mb-0">&copy; 2026 Al Shifa Healthcare. Dedicated to Community Wellness.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>
</body>
</html>