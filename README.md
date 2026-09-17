# FitCore Gym Management System

PPA Module project — Team **Apex Alliance** — Client: Mr. D. Dasanayaka | Supervisor: Mr. Anuruddha Abeysinghe

## Tech Stack
PHP (native, no framework) + MySQL (via XAMPP) + Tailwind CSS.

## Setup (each teammate, once)
1. Install [XAMPP](https://www.apachefriends.org/) if you don't have it.
2. Clone this repo **into your XAMPP `htdocs` folder** so the path is `C:\xampp\htdocs\fitcore`.
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Import the database: open phpMyAdmin (`http://localhost/phpmyadmin`) → Import → choose `database/schema.sql` → Go.
   (This creates the `fitcore` database with all tables and demo data.)
5. Visit `http://localhost/fitcore/auth/login.php` in your browser.
   - Demo admin login: `admin@fitcore.lk` / `Admin@123`

## Project Structure
```
auth/            Login / logout
config/db.php    Database connection
includes/        Shared layout, sidebar, reusable UI components
modules/         One folder per feature — this is where each person works
public/          Public marketing website + enquiry form
database/        schema.sql (import this to set up MySQL)
```

## Module Ownership
| Folder | Feature | Owner |
|---|---|---|
| `modules/members` | Member Management | Sajatha |
| `modules/employees`, `modules/attendance` | Employee Directory, Staff Attendance & Leave | Hasith |
| `modules/trainers`, `modules/classes` | Trainers & Schedule, Class/PT Booking | Senuka |
| `modules/plans`, `modules/subscriptions` | Membership Plans, Subscriptions & Renewal | Methul |
| `modules/payments` | Payment Submission & Verification | Asiri |
| `modules/equipment`, `modules/dashboard` | Equipment & Maintenance, Admin Dashboard | Janith |

**Please only edit files inside your own module folder** to avoid merge conflicts with teammates.

## Workflow
1. Open GitHub Desktop → make sure you're on the `main` branch and it's up to date (Fetch origin).
2. Edit files inside your own `modules/<your-area>` folder.
3. In GitHub Desktop: review your changed files → write a short commit summary → **Commit to main** → **Push origin**.
