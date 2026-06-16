## Project Documentation — Al Shifa Clinic Portal

This document describes the code structure, main pages, required database schema, and notes for running and maintaining the project.

**Project overview**
- Purpose: Simple clinic management portal for doctors, patients and admin.
- Stack: PHP (server-rendered UI), MySQL for persistence, small Node.js services in subfolders for background or API tasks.

**Top-level folders & purpose**
- `index.php` — Public landing page.
- `login.php`, `register.php`, `logout.php`, `verify.php` — Authentication flows.
- `doctor-dashboard.php` — Doctor panel: view and manage appointments, export CSV, view payment proof, update status and prescriptions.
- `patient-dashboard.php` — Patient panel (appointments, payments, etc.).
- `admin.php`, `admin-appointments.php` — Admin interfaces.
- `dataconnect/` — GraphQL connector and schema files (`schema/`, `operations.gql`, `connector.yaml`).
- `functions/`, `health/`, `healthcare/` — Node.js projects (each contains `package.json` and `index.js`) used for specialized tasks or endpoints.
- `public/` — Static site / static assets (e.g., `index.html`).
- `uploads/` — Uploaded files: receipts, doctors' images, screenshots.

**Important PHP pages (short descriptions)**
- `doctor-dashboard.php`: connects to MySQL with mysqli, checks session role `doctor`, provides CSV export, deletion, appointment updates, and displays a table of appointments. Uses direct SQL queries — consider converting to prepared statements.
- `register.php` / `login.php`: handle user creation and authentication.
- `appointment-related pages`: `payment.php`, `update-status.php`, `delete_appointment.php` handle appointment payment flow and state changes.

**Database: recommended minimal schema**
-- users table (example):

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
  image VARCHAR(512),
  specialization VARCHAR(255),
  phone VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

-- appointments table (example):

```sql
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doctor_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(255),
  app_date DATETIME,
  problem TEXT,
  status ENUM('Pending','Completed') DEFAULT 'Pending',
  fee_status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
  payment_screenshot VARCHAR(512),
  meeting_link VARCHAR(512),
  prescription_text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);
```

Adjust field sizes and indices for production needs.

**Where credentials are configured**
- Many PHP files open a MySQL connection directly like: `mysqli_connect("localhost", "root", "", "alshifa-db")` (see `doctor-dashboard.php`). Consolidate this into a single `config.php` that reads from environment variables or a local `.env` file (do not commit secrets).

**Uploads handling**
- Uploaded files are stored under `uploads/` (subfolders `doctors/`, `receipts/`, `screenshots/`). Ensure `uploads/` has correct file system permissions and that only allowed file types are accepted.

**Node services**
- Each Node subfolder has its own `package.json`. Typical workflow:
  - `npm install`
  - `npm start` (or `node index.js`)
- Inspect `index.js` inside each folder to learn what endpoints or background tasks are implemented.

**CSV export**
- `doctor-dashboard.php` has a CSV export endpoint (`?export=1`) that streams `appointments` rows for the logged-in doctor.

**Security & hardening checklist**
- Move DB credentials to an external config and avoid hardcoding them.
- Replace raw SQL string building with parameterized queries (PDO or mysqli prepared statements).
- Escape all output when printing user content: use `htmlspecialchars()` (already used in several places) consistently.
- Validate and sanitize uploaded files. Store outside web root or protect direct access with per-request checks.
- Add CSRF tokens for sensitive POST actions (updates, deletes).
- Rate-limit authentication endpoints and enforce strong password rules.

**Testing & debugging**
- Enable `error_reporting(E_ALL); ini_set('display_errors', 1);` only in development. In production, turn off display and log errors to files.

**Deployment suggestions**
- For small-scale deployment, use a LAMP host with HTTPS. Configure `php.ini` and `MySQL` for production.
- Use a process manager (PM2) for Node services.
- Backup MySQL regularly and protect backups with encryption.

**Maintenance & next improvements**
- Centralize DB connection and configuration.
- Add unit/integration tests for Node services.
- Consider migrating to a single API backend (Node or PHP) and convert UI to use API endpoints for clearer separation.

If you want, I can:
- generate a `config.sample.php` and `.env` example,
- convert key DB queries to prepared statements,
- or create a deploy checklist / docker-compose file.
