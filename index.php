<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Database Connection Block with Dynamic Fallback Environment
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'alshifa-db';
$port     = getenv('MYSQLPORT') ?: '3306';

$conn = mysqli_connect($host, $user, $password, $database, $port);

// Dynamic Query to fetch active healthcare services/specialties
$specialties_result = null;
if ($conn) {
    $specialties_result = mysqli_query($conn, "SELECT * FROM specialties LIMIT 3");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al Shifa HealthCare | Modern Medical Portal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4042a1;
            --secondary: #5f61d8;
            --accent: #00d2ff;
            --dark-blue: #1a1b4b;
            --light-bg: #f8fafd;
            --card-shadow: 0 20px 40px rgba(64, 66, 161, 0.08);
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--light-bg); 
            color: #2d3436; 
            overflow-x: hidden; 
        }

        /* Premium Floating Glassmorphic Navbar */
        .navbar { 
            background: rgba(64, 66, 161, 0.9) !important; 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 15px 0;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .navbar-brand { font-weight: 800; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; transition: color 0.2s; }
        .nav-link:hover, .nav-link.active { color: var(--accent) !important; }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: var(--primary);
                margin-top: 15px;
                padding: 25px;
                border-radius: 20px;
                box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            }
        }

        /* Cinematic Hero Section */
        .hero-header { 
            position: relative;
            min-height: 90vh;
            background: linear-gradient(135deg, rgba(26, 27, 75, 0.85), rgba(64, 66, 161, 0.75)), 
                        url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 93%, 0% 100%);
        }
        .hero-content { z-index: 2; position: relative; }
        
        /* Modernized Service Cards & Specialties Grid */
        .service-card { 
            background: white; 
            border-radius: 24px; 
            padding: 30px; 
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
            border: 1px solid rgba(64, 66, 161, 0.05);
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }
        .service-card:hover { 
            transform: translateY(-10px); 
            box-shadow: var(--card-shadow);
            border-color: rgba(95, 97, 216, 0.2);
        }
        .service-icon-box {
            width: 60px; height: 60px;
            background: rgba(64, 66, 161, 0.06);
            color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px; margin-bottom: 20px;
            font-size: 1.5rem; transition: 0.3s;
        }
        .service-card:hover .service-icon-box {
            background: var(--primary);
            color: white;
        }

        /* AI Container Glassmorphism Redesign */
        .shifa-bot-container {
            background: white;
            border-radius: 30px;
            padding: 35px;
            border: 1px solid rgba(64, 66, 161, 0.08);
            box-shadow: var(--card-shadow);
            position: relative;
        }
        .shifa-bot-container::before {
            content: ''; position: absolute; top: 0; left: 30px; right: 30px; height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;
        }

        .video-call-box {
            background: linear-gradient(135deg, #eef3ff 0%, #dbe4ff 100%);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            overflow: hidden;
            height: 100%;
            display: flex; flex-direction: column; justify-content: space-between;
        }

        /* Pulse Core Buttons */
        .btn-pulse {
            position: relative; animation: pulse 2.5s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255,255,255, 0.5); }
            70% { box-shadow: 0 0 0 20px rgba(255,255,255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255,255,255, 0); }
        }

        /* AI Streaming Output Style fixes */
        #aiResponse ul { padding-left: 20px; margin-bottom: 0; }
        #aiResponse li { margin-bottom: 8px; color: #4b5563; }

        footer { 
            background: var(--dark-blue); 
            padding: 70px 0 30px; 
            color: #a0aec0; 
            border-top-left-radius: 40px; border-top-right-radius: 40px;
        }
        
        @media (max-width: 768px) {
            .hero-header h1 { font-size: 36px; }
            .hero-header { min-height: 70vh; clip-path: none; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="index.php">
        <i class="bi bi-heart-pulse-fill me-2 text-info"></i> AL SHIFA
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
        <li class="nav-item"><a class="nav-link" href="vediocall.php"><i class="bi bi-camera-video-fill text-info fs-5 px-2"></i></a></li> 

        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown ms-lg-3">
                <a class="btn btn-info rounded-pill px-4 btn-sm fw-bold dropdown-toggle text-white shadow-sm" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 animate__animated animate__fadeIn">
                    <li class="dropdown-header small text-uppercase text-muted fw-bold">User System Menu</li>
                    <?php 
                    $role = $_SESSION['user_role'] ?? '';
                    if($role == 'admin'): ?>
                        <li><a class="dropdown-item fw-bold text-primary" href="admin-dashboard.php"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
                    <?php elseif($role == 'doctor'): ?>
                        <li><a class="dropdown-item" href="doctor-dashboard.php"><i class="bi bi-calendar-check me-2"></i>Doctor Dashboard</a></li>
                    <?php endif; ?>
                    
                    <li><a class="dropdown-item" href="patient-dashboard.php"><i class="bi bi-calendar2-heart me-2"></i>My Appointments</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item ms-lg-3">
                <a class="btn btn-light rounded-pill px-4 btn-sm fw-bold shadow-sm d-inline-block" href="login.php">
                    <i class="bi bi-person-circle me-1"></i> Account Portal
                </a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<header class="hero-header">
  <div class="container hero-content text-center text-lg-start">
    <div class="row align-items-center g-5">
      <div class="col-lg-7" data-aos="fade-right" data-aos-duration="1200">
        <span class="badge bg-info mb-3 px-3 py-2 text-uppercase fw-bold shadow-sm">Trusted Healthcare Excellence</span>
        <h1 class="display-3 fw-bold mb-3 tracking-tight text-white">Your Health, Our <span class="text-info">Absolute</span> Priority</h1>
        <p class="lead mb-4 opacity-90 fs-5">Experience next-generation digital healthcare infrastructure at Ada Sikandri Lodhran with ShifaBot AI diagnostics and encrypted remote video consultations.</p>
        <div class="d-flex justify-content-center justify-content-lg-start gap-3">
            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow btn-pulse text-primary">Book Appointment</a>
            <a href="#specialties-section" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">Our Medical Wings</a>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Dynamic Medical Wings Section -->
<section id="specialties-section" class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary fw-bold text-uppercase tracking-wider">Clinical Departments</h6>
            <h2 class="display-6 fw-bold">Our Top Tier Specialties</h2>
        </div>
        <div class="row g-4">
            <?php 
            if ($specialties_result && mysqli_num_rows($specialties_result) > 0): 
                while($specialty = mysqli_fetch_assoc($specialties_result)):
            ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="service-card">
                        <div class="service-icon-box">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($specialty['name'] ?? 'Medical Wing'); ?></h4>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($specialty['description'] ?? 'High-end therapeutic solutions powered by top-tier clinical machinery.'); ?></p>
                    </div>
                </div>
            <?php 
                endwhile;
            else: 
            ?>
                <!-- Static UI Safe Fallbacks if Database yields null results -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="service-card">
                        <div class="service-icon-box"><i class="bi bi-heart-pulse"></i></div>
                        <h4 class="fw-bold mb-3">Cardiology Care</h4>
                        <p class="text-muted small mb-0">High precision heart sync assessments, electrocardiography profiles, and clinical management parameters.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-icon-box"><i class="bi bi-capsule"></i></div>
                        <h4 class="fw-bold mb-3">Internal Medicine</h4>
                        <p class="text-muted small mb-0">Thorough biological system diagnostics, infectious dynamic defense, and precise physiological oversight.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-icon-box"><i class="bi bi-baby"></i></div>
                        <h4 class="fw-bold mb-3">Pediatrics Department</h4>
                        <p class="text-muted small mb-0">Infant biological care monitoring, dynamic neonate tracking structures, and child vaccination security templates.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Structural Breakdown Overview -->
<section class="py-5 border-top border-light bg-white">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="position-relative">
            <img src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-5 shadow-lg" alt="Clinic Overview">
            <div class="position-absolute bottom-0 start-0 bg-white p-3 rounded-4 m-3 shadow-lg d-none d-md-block border border-light">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-star-fill text-warning me-1"></i> 4.9/5 Rating</h6>
                <small class="text-muted">From 2,000+ Verified Patient Portals</small>
            </div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <h6 class="text-primary fw-bold text-uppercase tracking-wider">Who We Are</h6>
        <h2 class="display-5 fw-bold mb-4 text-dark">Dedicated to a <span class="text-primary">Healthier Community</span></h2>
        <p class="text-muted lead">Al Shifa Medical Centre combines human expertise with modern diagnostic architecture to provide seamless healthcare infrastructure inside Lodhran.</p>
        <div class="row g-3 mb-4">
            <div class="col-12 d-flex align-items-center">
                <i class="bi bi-shield-check text-success fs-4 me-3"></i>
                <span class="fw-semibold text-dark">24/7 Intensive Emergency Critical Care Support</span>
            </div>
            <div class="col-12 d-flex align-items-center">
                <i class="bi bi-cpu text-success fs-4 me-3"></i>
                <span class="fw-semibold text-dark">AI-Powered Rapid Symptom Screening Layer</span>
            </div>
            <div class="col-12 d-flex align-items-center">
                <i class="bi bi-person-hearts text-success fs-4 me-3"></i>
                <span class="fw-semibold text-dark">Certified Senior Consultants and Professional Nurses</span>
            </div>
        </div>
        <a href="about.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">Learn More About Us</a>
      </div>
    </div>
  </div>
</section>

<!-- Remote Diagnostics & Smart Intelligence Systems Layer -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5" data-aos="zoom-in">
        <div class="video-call-box p-4 text-center shadow-sm">
          <div class="p-4">
            <div class="text-primary display-4 mb-3"><i class="bi bi-camera-video-fill"></i></div>
            <h3 class="fw-bold text-dark">Virtual TeleHealth Consultation</h3>
            <p class="text-muted small">Skip the waiting queue. Discuss your pathological symptoms securely with certified doctors directly from home.</p>
            <a href="patient-dashboard.php" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm w-100 py-2 mt-2">Launch Tele-Clinic</a>
          </div>
          <div class="mt-2 text-center bg-white p-3 rounded-4 mx-3 mb-3 border border-white">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209109.png" style="width: 90px;" alt="Video Graphics Container">
          </div>
        </div>
      </div>

      <div class="col-lg-7" data-aos="zoom-in" data-aos-delay="200">
        <div class="shifa-bot-container h-100 bg-white">
          <div class="d-flex align-items-center mb-4">
            <div class="bg-primary text-white rounded-4 p-3 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                <i class="bi bi-robot fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 text-dark">ShifaBot Clinical AI</h3>
                <small class="text-success fw-bold d-flex align-items-center"><i class="bi bi-circle-fill fs-6 me-1" style="font-size: 8px !important;"></i> Engine Ready</small>
            </div>
          </div>
          <p class="text-muted small mb-3">Input clinical patterns or diagnostic inquiries below for real-time risk mitigation screening.</p>
          <textarea id="symptom" class="form-control border-0 bg-light mb-3 p-3 rounded-4 fs-6" style="resize: none;" rows="3" placeholder="Describe clinical symptoms (e.g., chronic headache, persistent dry cough with localized fatigue)..."></textarea>
          <button id="checkBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">Analyze Pathological State</button>
          <div id="aiResponse"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container text-center">
    <h2 class="fw-bold mb-2 text-white"><i class="bi bi-shield-plus text-info me-2"></i>AL SHIFA MEDICAL CENTRE</h2>
    <p class="opacity-75 small mb-4">Ada Sikandri 49/M, National Highway, Lodhran, Punjab, Pakistan</p>
    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-facebook"></i></a>
        <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-whatsapp"></i></a>
        <a href="#" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1"><i class="bi bi-envelope"></i></a>
    </div>
    <hr class="opacity-25 mb-4">
    <p class="small opacity-50 mb-0">&copy; 2026 Al Shifa Healthcare Infrastructure. Integrated Community Diagnostics.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ once: true, duration: 800 });

  const checkBtn = document.getElementById("checkBtn");
  const aiResponseDiv = document.getElementById("aiResponse");
  const symptomInput = document.getElementById("symptom");

  checkBtn.addEventListener("click", async () => {
    const symptom = symptomInput.value.trim();
    if (!symptom) return alert("Please clarify patterns inside the query field.");

    checkBtn.disabled = true;
    checkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Computing Bio-Metrics...';
    
    try {
      const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
        method: "POST",
        headers: { 
          "Content-Type": "application/json",
          "Authorization": `Bearer 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`
        },
        body: JSON.stringify({
          model: "llama-3.3-70b-versatile",
          messages: [
            { role: "system", content: "You are ShifaBot. Professional, warm clinical digital helper. Always format responses using proper bullet points or distinct short paragraphs. Be concise. Mandatory disclaimer: This evaluation is purely structural, not a physical substitute for an absolute on-site clinical diagnostic execution." },
            { role: "user", content: symptom }
          ]
        })
      });

      const data = await response.json();
      let advice = data.choices[0].message.content;

      // Clean Markdown parser transformations
      advice = advice.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      advice = advice.replace(/(?:\r\n|\r|\n)/g, '<br>');
      advice = advice.replace(/<br>\s*-\s*(.*?)/g, '<li>$1</li>');
      advice = advice.replace(/<br>\s*\*\s*(.*?)/g, '<li>$1</li>');

      aiResponseDiv.innerHTML = `
        <div class="card border-0 shadow-sm mt-3 animate__animated animate__fadeIn" style="background: #f4f7ff; border-radius: 20px;">
          <div class="card-body p-4">
            <h6 class="text-primary fw-bold mb-3 d-flex align-items-center"><i class="bi bi-activity text-danger me-2"></i>AI Diagnostic Assessment Output:</h6>
            <div class="text-dark fs-6" style="line-height: 1.7;">
              ${advice}
            </div>
            <div class="mt-3 pt-3 border-top small text-muted fst-italic">
                Emergency Alert: If symptoms match critical cardiovascular or respiratory pressure drops, please contact the Al Shifa on-site emergency wing immediately.
            </div>
          </div>
        </div>`;
    } catch (err) {
      aiResponseDiv.innerHTML = `<div class="alert alert-danger mt-3 rounded-4 small">Secure diagnostics pipeline timeout. Please re-verify Groq credentials or server network ports.</div>`;
    } finally {
      checkBtn.disabled = false;
      checkBtn.innerHTML = "Analyze Pathological State";
    }
  });
</script>
</body>
</html>