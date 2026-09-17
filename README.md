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
| Folder | Feature | Owner | Your branch name |
|---|---|---|---|
| `modules/members` | Member Management | Sajatha | `sajatha-members` |
| `modules/employees`, `modules/attendance` | Employee Directory, Staff Attendance & Leave | Hasith | `hasith-employees-attendance` |
| `modules/trainers`, `modules/classes` | Trainers & Schedule, Class/PT Booking | Senuka | `senuka-trainers-classes` |
| `modules/plans`, `modules/subscriptions` | Membership Plans, Subscriptions & Renewal | Methul | (works on `main`) |
| `modules/payments` | Payment Submission & Verification | Asiri | `asiri-payments` |
| `modules/equipment`, `modules/dashboard` | Equipment & Maintenance, Admin Dashboard | Janith | `janith-equipment-dashboard` |

**Please only edit files inside your own module folder** to avoid merge conflicts with teammates.

## Workflow (branch + Pull Request — everyone except Methul)
Each person works on their **own branch** (named above) and merges it in themselves through a Pull Request. This keeps individual contributions clearly separated and visible in the repo's history.

1. Open GitHub Desktop → top bar → **Current Branch** → **New Branch** → name it exactly as listed in the table above → **Create Branch** (this branches off `main`).
2. Edit files inside your own `modules/<your-area>` folder only.
3. Left panel will show your changed files → write a short commit summary → **Commit to `<your-branch>`**.
4. Click **Push origin** (top bar).
5. Go to the repo on github.com — a yellow banner will say **"Compare & pull request"** for your branch → click it.
6. Write a one-line description of what you changed → **Create Pull Request**.
7. On the Pull Request page, click **Merge pull request** → **Confirm merge**. Your branch is now merged into `main`.
8. Back in GitHub Desktop: switch **Current Branch** back to `main` → **Fetch origin** to pull in everyone's merged work.

If you need to make more changes later, repeat from step 1 using the same branch name (or a new one) — GitHub Desktop will let you switch back to it under **Current Branch**.
