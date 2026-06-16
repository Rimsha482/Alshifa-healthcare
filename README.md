# Al Shifa — Clinic Portal

A lightweight clinic portal built with PHP (frontend) and small Node.js services. Designed to run on a local LAMP/XAMPP stack and complementary Node services in subfolders.

**Quick summary**
- PHP pages: patient/doctor/admin UI and flows
- Node services: serverless-style helpers in `functions/`, `health/`, `healthcare/`
- Database: MySQL (database name used in code: `alshifa-db`)

**Prerequisites**
- XAMPP (Apache + MySQL) or any PHP 8.x + MySQL setup
- Node.js (16+) for running local services in subfolders

**Local setup (quick)**
1. Place repository inside your web server root (e.g., `C:/xampp/htdocs/alshifa-portal` or `e:/xamp/htdocs/alshifa-portal`).
2. Start Apache and MySQL via XAMPP.
3. Create a database named `alshifa-db` and import your SQL schema (see `docs/PROJECT_DOCUMENTATION.md` for example schema).
4. Open `http://localhost/alshifa-portal/` in the browser.

**Node services**
Run in each Node folder (if needed):

```bash
cd functions
npm install
npm start

cd ../health
npm install
npm start

cd ../healthcare
npm install
npm start
```

**Important files & pages**
- [index.php](index.php) — public landing page
- [login.php](login.php), [register.php](register.php) — authentication
- [doctor-dashboard.php](doctor-dashboard.php) — doctor interface (manage appointments)
- [patient-dashboard.php](patient-dashboard.php) — patient interface
- [admin.php](admin.php) — admin area
- [dataconnect/](dataconnect/) — GraphQL connector / schema and operations
- [functions/](functions/) — Node helpers and cloud functions

**Database (high-level)**
The app uses at least two main tables: `users` and `appointments`. Example minimal schemas are provided in `docs/PROJECT_DOCUMENTATION.md`.

**Security notes**
- Current codebase contains hardcoded DB credentials (e.g., `localhost`, `root`, no password) in some PHP files. Move these to a single config file or environment variables for production.
- Use prepared statements (PDO or `mysqli_prepare`) for database writes/reads to prevent SQL injection.
- Sanitize uploaded files and validate MIME types before serving.

**Contributing**
- Open an issue for bugs or features.
- Use a feature branch, test locally, and create a pull request with a clear description.

**License**
- Add a license file if you plan to publish this project publicly.

For full technical details, see [docs/PROJECT_DOCUMENTATION.md](docs/PROJECT_DOCUMENTATION.md).
