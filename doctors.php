<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Database Connection ---
$conn = mysqli_connect("localhost", "root", "", "alshifa-db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Doctors - Al Shifa HealthCare</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  
  <style>
    :root {
      --primary: #4042a1;
      --secondary: #5f61d8;
      --accent: #00d2ff;
      --light-bg: #f8fafd;
      --text-dark: #2d3436;
      --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: var(--light-bg);
      color: var(--text-dark);
      overflow-x: hidden;
    }

    /* Modern Responsive Navbar (Exact Home Page Layout) */
    .navbar { 
        background: rgba(64, 66, 161, 0.95) !important; 
        backdrop-filter: blur(10px);
        padding: 15px 0;
        transition: 0.3s;
    }
    .navbar-brand { font-weight: 800; letter-spacing: -1px; }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: var(--primary);
            margin-top: 15px;
            padding: 20px;
            border-radius: 15px;
        }
        .navbar-nav .nav-item { width: 100%; text-align: left; }
    }

    /* Hero Header */
    .hero-header {
      background: linear-gradient(rgba(64, 66, 161, 0.8), rgba(95, 97, 216, 0.8)), 
                  url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1920&q=80');
      background-attachment: fixed;
      background-size: cover;
      background-position: center;
      padding: 150px 0 120px;
      color: white;
      text-align: center;
      clip-path: ellipse(150% 100% at 50% 0%);
      margin-bottom: 50px;
    }

    /* Doctor Card */
    .doctor-card {
      background: white;
      border-radius: 24px;
      overflow: hidden;
      transition: var(--transition);
      border: 1px solid rgba(0,0,0,0.05);
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .doctor-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 20px 40px rgba(64, 66, 161, 0.15);
    }

    .doctor-img-wrapper {
      position: relative;
      overflow: hidden;
      height: 300px;
      background: #eef2ff;
    }

    .doctor-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: 0.5s;
    }

    .doctor-info {
      padding: 25px 20px;
      text-align: center;
      flex-grow: 1;
    }

    .specialty-badge {
        background: rgba(64, 66, 161, 0.1);
        color: var(--primary);
        font-weight: 700;
        font-size: 12px;
        padding: 5px 15px;
        border-radius: 50px;
        display: inline-block;
        margin-bottom: 15px;
    }

    .btn-book {
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 12px 25px;
      font-weight: 700;
      width: 100%;
      transition: var(--transition);
    }

    .btn-book:hover {
      background: var(--secondary);
      color: white;
    }

    footer {
      background: #1a1b4b;
      color: #bdc3c7;
      padding: 50px 0;
      margin-top: 80px;
    }
    
    @media (max-width: 768px) {
        .hero-header { min-height: 60vh; clip-path: none; padding: 100px 0 60px; }
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <i class="bi bi-heart-pulse-fill me-2 text-info"></i> AL SHIFA
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
        <li class="nav-item"><a class="nav-link" href="vediocall.php"><i class="bi bi-camera-video-fill pulse-icon fs-5"></i></a></li> 

        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown ms-lg-3">
                <a class="btn btn-info rounded-pill px-4 btn-sm fw-bold dropdown-toggle text-white shadow-sm" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate__animated animate__fadeIn">
                    <li class="dropdown-header small text-uppercase text-muted fw-bold">User Menu</li>
                    <?php 
                    $role = $_SESSION['user_role'] ?? '';
                    if($role == 'admin'): ?>
                        <li><a class="dropdown-item fw-bold text-primary" href="admin.php"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
                    <?php elseif($role == 'doctor'): ?>
                        <li><a class="dropdown-item" href="doctor-dashboard.php"><i class="bi bi-calendar-check me-2"></i>Doctor Dashboard</a></li>
                    <?php endif; ?>
                    
                    <li><a class="dropdown-item" href="patient-dashboard.php"><i class="bi bi-calendar2-heart me-2"></i>My Appointments</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item ms-lg-3 w-auto">
                <a class="btn btn-light rounded-pill px-3 btn-sm fw-bold shadow-sm d-inline-block" href="login.php">
                    <i class="bi bi-person-circle me-1"></i> Account
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<header class="hero-header">
  <div class="container" data-aos="zoom-out">
    <span class="badge bg-info mb-3 px-3 py-2 text-uppercase fw-bold shadow-sm">Expert Medical Team</span>
    <h1 class="display-4 fw-bold">Meet Our Specialists</h1>
    <p class="lead opacity-75">World-class healthcare from Lodhran's finest professionals.</p>
  </div>
</header>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <?php
      // --- Fetching Approved Doctors ---
      $query = "SELECT * FROM users WHERE role = 'doctor' AND status = 1";
      $result = mysqli_query($conn, $query);

      if($result && mysqli_num_rows($result) > 0):
          while($dr = mysqli_fetch_assoc($result)): 
              // Image logic: if empty, show default placeholder
              $dr_img = (!empty($dr['image'])) ? $dr['image'] : 'https://cdn-icons-png.flaticon.com/512/387/387561.png';
      ?>
      <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up">
        <div class="doctor-card">
          <div class="doctor-img-wrapper text-center d-flex align-items-center justify-content-center">
            <img src="<?php echo $dr_img; ?>" class="doctor-img" alt="<?php echo $dr['name']; ?>">
          </div>
          <div class="doctor-info">
            <span class="specialty-badge"><?php echo htmlspecialchars($dr['specialization'] ?? 'General Physician'); ?></span>
            <h4 class="doctor-name"> <?php echo htmlspecialchars($dr['name']); ?></h4>
            <p class="text-muted small mb-4">
                <?php 
                $desc = $dr['description'] ?? 'Dedicated specialist providing expert medical care.';
                echo htmlspecialchars(substr($desc, 0, 80)) . '...'; 
                ?>
            </p>
            <button class="btn btn-book" onclick="location.href='contact.php?dr_id=<?php echo $dr['id']; ?>'">
              Book Appointment
            </button>
          </div>
        </div>
      </div>
      <?php 
          endwhile;
      else:
          echo "<div class='col-12 text-center'><p class='lead text-muted'>Filhal koi approved doctor system mein nahi hai.</p></div>";
      endif; 
      ?>
    </div>
  </div>
</section>

<footer>
  <div class="container text-center">
    <h2 class="fw-bold mb-3"><i class="bi bi-shield-plus me-2"></i>AL SHIFA</h2>
    <p class="opacity-75 mb-4">Ada Sikandri 49/M, Lodhran, Punjab, Pakistan</p>
    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="#" class="btn btn-outline-light rounded-circle p-2"><i class="bi bi-facebook"></i></a>
        <a href="#" class="btn btn-outline-light rounded-circle p-2"><i class="bi bi-whatsapp"></i></a>
        <a href="#" class="btn btn-outline-light rounded-circle p-2"><i class="bi bi-envelope"></i></a>
    </div>
    <p class="small opacity-50">&copy; 2026 Al Shifa Healthcare. Community First Wellness.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>
</body>
</html>