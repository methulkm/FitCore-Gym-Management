-- =====================================================================
-- FitCore Gym Management System - Database Schema
-- Apex Alliance | PPA Module | SLIIT City Campus
--
-- Derived from the FitCore SRS (business rules BR-01..BR-22, FR-01..FR-21,
-- UC-01..UC-18). Every ENUM / rule below is traceable to a specific BR/FR.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS fitcore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitcore;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- USERS  (login accounts only - BR-01, BR-03: employees never get one)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(150) NOT NULL UNIQUE,     -- login username (SRS 2.7)
    password_hash VARCHAR(255) NOT NULL,             -- NFR-S01: never plain text
    role          ENUM('admin','member','trainer') NOT NULL,
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- MEMBERS  (BR-01, BR-02, UC-02)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS members;
CREATE TABLE members (
    member_id     INT AUTO_INCREMENT PRIMARY KEY,
    member_code   VARCHAR(20) NOT NULL UNIQUE,       -- BR-02: MEM001, MEM002, ...
    user_id       INT NULL,                          -- login account (admin-created)
    full_name     VARCHAR(120) NOT NULL,
    nic_passport  VARCHAR(30) NOT NULL,
    phone         VARCHAR(20) NOT NULL,
    address       VARCHAR(255),
    email         VARCHAR(150),
    join_date     DATE NOT NULL,
    account_status ENUM('active','deactivated') NOT NULL DEFAULT 'active', -- admin-controlled (2.8.3)
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------
-- EMPLOYEES  (BR-03, BR-17: cleaners / receptionists / maintenance, no login)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS employees;
CREATE TABLE employees (
    employee_id   INT AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) NOT NULL UNIQUE,       -- EMP001, EMP002, ...
    full_name     VARCHAR(120) NOT NULL,
    phone         VARCHAR(20) NOT NULL,
    job_role      ENUM('cleaner','receptionist','maintenance') NOT NULL,
    shift         ENUM('morning','evening','general') NOT NULL DEFAULT 'general',
    status        ENUM('active','on_leave','inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- EMPLOYEE ATTENDANCE  (BR-17, mirrors BR-15 pattern: check-in only)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS employee_attendance;
CREATE TABLE employee_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time TIME NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE KEY uq_emp_att (employee_id, attendance_date)
);

-- ---------------------------------------------------------------------
-- EMPLOYEE LEAVE  (BR-17)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS employee_leave;
CREATE TABLE employee_leave (
    leave_id      INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    leave_type    ENUM('medical','casual') NOT NULL,
    start_date    DATE NOT NULL,
    end_date      DATE NOT NULL,
    reason        VARCHAR(255),
    status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- TRAINERS  (trainers DO get a login account, unlike employees - UC-05)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS trainers;
CREATE TABLE trainers (
    trainer_id     INT AUTO_INCREMENT PRIMARY KEY,
    trainer_code   VARCHAR(20) NOT NULL UNIQUE,      -- TRN001, TRN002, ...
    user_id        INT NULL,
    full_name      VARCHAR(120) NOT NULL,
    email          VARCHAR(150),
    phone          VARCHAR(20),
    specialization VARCHAR(120),
    qualification  VARCHAR(255),
    profile_image  VARCHAR(255),
    status         ENUM('active','deactivated') NOT NULL DEFAULT 'active',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------
-- TRAINER AVAILABILITY  (UC-06: simple day + start/end time, no recurrence)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS trainer_availability;
CREATE TABLE trainer_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id      INT NOT NULL,
    day_date        DATE NOT NULL,
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id) ON DELETE CASCADE,
    CHECK (end_time > start_time)
);

-- ---------------------------------------------------------------------
-- MEMBERSHIP PLANS  (BR-05, BR-06: deactivate, never delete once referenced)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS membership_plans;
CREATE TABLE membership_plans (
    plan_id         INT AUTO_INCREMENT PRIMARY KEY,
    plan_name       VARCHAR(80) NOT NULL,
    duration_months INT NOT NULL,
    price           DECIMAL(10,2) NOT NULL,
    description     VARCHAR(255),
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('active','deactivated') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- SUBSCRIPTIONS  (BR-04, BR-08, BR-09: expiring-soon computed, not stored)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS subscriptions;
CREATE TABLE subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id       INT NOT NULL,
    plan_id         INT NOT NULL,
    start_date      DATE NOT NULL,
    expiry_date     DATE NOT NULL,
    status          ENUM('active','expired','deactivated') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id)
);

-- ---------------------------------------------------------------------
-- PAYMENTS  (BR-07, BR-08, BR-09, NFR-P04: max 5MB slip, NFR-S04)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    payment_id       INT AUTO_INCREMENT PRIMARY KEY,
    payment_code     VARCHAR(20) NOT NULL UNIQUE,     -- PAY1001, PAY1002, ...
    member_id        INT NOT NULL,
    plan_id          INT NOT NULL,
    subscription_id  INT NULL,                        -- filled in once approved
    amount           DECIMAL(10,2) NOT NULL,
    transfer_date    DATE NOT NULL,
    reference_number VARCHAR(80) NOT NULL,
    slip_path        VARCHAR(255) NOT NULL,
    status           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason VARCHAR(255) NULL,
    verified_by      INT NULL,                        -- admin user_id (UC-13)
    verified_at      TIMESTAMP NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------
-- CLASSES  (BR-11, BR-22: admin sets capacity, no auto-recurrence)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS classes;
CREATE TABLE classes (
    class_id        INT AUTO_INCREMENT PRIMARY KEY,
    class_name      VARCHAR(120) NOT NULL,
    trainer_id      INT NOT NULL,
    class_date      DATE NOT NULL,
    start_time      TIME NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 60,
    capacity        INT NOT NULL,
    status          ENUM('scheduled','cancelled','completed') NOT NULL DEFAULT 'scheduled',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id)
);

-- ---------------------------------------------------------------------
-- BOOKINGS  (BR-10, BR-12, BR-13, BR-14: class OR 1-hour PT, 2h cancel rule)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS bookings;
CREATE TABLE bookings (
    booking_id    INT AUTO_INCREMENT PRIMARY KEY,
    member_id     INT NOT NULL,
    class_id      INT NULL,                          -- set when booking_type = 'class'
    trainer_id    INT NULL,                          -- set when booking_type = 'pt'
    booking_type  ENUM('class','pt') NOT NULL,
    slot_date     DATE NOT NULL,
    start_time    TIME NOT NULL,
    status        ENUM('booked','cancelled','completed') NOT NULL DEFAULT 'booked',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id)
);

-- ---------------------------------------------------------------------
-- MEMBER ATTENDANCE  (BR-15, BR-16: check-in only, no hardware)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS member_attendance;
CREATE TABLE member_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id     INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time TIME NOT NULL,
    recorded_by   INT NULL,                          -- admin user_id
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY uq_mem_att (member_id, attendance_date)
);

-- ---------------------------------------------------------------------
-- EQUIPMENT  (BR-18, BR-19: major equipment only, no per-unit stock)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS equipment;
CREATE TABLE equipment (
    equipment_id   INT AUTO_INCREMENT PRIMARY KEY,
    equipment_code VARCHAR(20) NOT NULL UNIQUE,       -- EQP001, EQP002, ...
    equipment_name VARCHAR(120) NOT NULL,
    supplier       VARCHAR(120),
    purchase_date  DATE,
    condition_status VARCHAR(60),
    location       VARCHAR(60),
    status         ENUM('available','in_use','under_maintenance','out_of_service') NOT NULL DEFAULT 'available',
    notes          VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- EQUIPMENT MAINTENANCE  (maintenance history + next due date)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS equipment_maintenance;
CREATE TABLE equipment_maintenance (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id   INT NOT NULL,
    maintenance_date DATE NOT NULL,
    next_due_date  DATE,
    notes          VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- ENQUIRIES  (FR-02, UC-18: visitor contact, no account created)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS enquiries;
CREATE TABLE enquiries (
    enquiry_id   INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120) NOT NULL,
    phone        VARCHAR(20),
    email        VARCHAR(150),
    message      TEXT NOT NULL,
    status       ENUM('new','reviewed','closed') NOT NULL DEFAULT 'new',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA - enough for every module's viva demo (2+ CRUD ops each)
-- =====================================================================

-- Admin login: admin@fitcore.lk / password: Admin@123
INSERT INTO users (email, password_hash, role, status) VALUES
('admin@fitcore.lk', '$2y$10$5FD20FPu73gUZYdpBCy0qeXyfQKHTG97wBHLgBcE2YjD9Lw.U/FUW', 'admin', 'active');
-- NOTE: hash above is a real bcrypt hash for 'Admin@123', generated via PHP password_hash().

-- description stores perks pipe-separated ("Perk one|Perk two"), rendered as a bullet list.
INSERT INTO membership_plans (plan_name, duration_months, price, description, is_featured, status) VALUES
('Monthly Starter', 1, 5000.00, 'Gym Floor Access|Locker Room', 0, 'active'),
('Silver Fitness', 3, 13500.00, 'Gym Floor Access|1 Free PT Consult', 0, 'active'),
('Gold Pro Pass', 6, 24000.00, 'Full Gym + Classes|2 Free PT Sessions', 1, 'active'),
('Platinum Elite', 12, 42000.00, 'VIP Gym Access|6 Free PT Sessions', 0, 'active');

INSERT INTO members (member_code, full_name, nic_passport, phone, address, email, join_date, account_status) VALUES
('MEM001', 'Sanduni Perera', '199823456789', '0771234567', '12 Galle Road, Colombo 03', 'sanduni@example.com', CURDATE() - INTERVAL 4 MONTH, 'active'),
('MEM002', 'Kasun Nimalka', '199512345678', '0779876543', '45 Union Place, Colombo 02', 'kasun@example.com', CURDATE() - INTERVAL 8 MONTH, 'active'),
('MEM003', 'Nadeesha Silva', '199712345000', '0712223344', '8 Marine Drive, Colombo 06', 'nadeesha@example.com', CURDATE(), 'active');

-- Dates relative to CURDATE() so the "Expiring Soon" (BR-04) demo always looks right, whenever this is run.
INSERT INTO subscriptions (member_id, plan_id, start_date, expiry_date, status) VALUES
(1, 2, CURDATE() - INTERVAL 3 MONTH, CURDATE() + INTERVAL 3 DAY, 'active'),
(2, 3, CURDATE() - INTERVAL 2 MONTH, CURDATE() + INTERVAL 4 MONTH, 'active');

INSERT INTO employees (employee_code, full_name, phone, job_role, shift, status) VALUES
('EMP001', 'Nimal Fernando', '0761112233', 'cleaner', 'morning', 'active'),
('EMP002', 'Dilani Rathnayake', '0765556677', 'receptionist', 'general', 'active'),
('EMP003', 'Sunil Bandara', '0778889900', 'maintenance', 'evening', 'active');

INSERT INTO trainers (trainer_code, full_name, email, phone, specialization, qualification, status) VALUES
('TRN001', 'Ruwan Gamage', 'ruwan@fitcore.lk', '0711234567', 'CrossFit', 'ACE Certified Trainer', 'active'),
('TRN002', 'Anuki Fernando', 'anuki@fitcore.lk', '0719876543', 'Yoga', 'RYT-200 Yoga Alliance', 'active'),
('TRN003', 'Kavinda Silva', 'kavinda@fitcore.lk', '0713334455', 'Bodybuilding', 'NASM-CPT', 'active');

INSERT INTO classes (class_name, trainer_id, class_date, start_time, duration_minutes, capacity, status) VALUES
('Power CrossFit', 1, CURDATE() + INTERVAL 1 DAY, '08:00:00', 60, 20, 'scheduled'),
('Vinyasa Sunset Yoga', 2, CURDATE() + INTERVAL 1 DAY, '17:00:00', 60, 25, 'scheduled'),
('Hypertrophy Camp', 3, CURDATE() + INTERVAL 2 DAY, '18:30:00', 60, 15, 'scheduled');

INSERT INTO equipment (equipment_code, equipment_name, supplier, purchase_date, condition_status, location, status) VALUES
('EQP001', 'Matrix Treadmill T50', 'Matrix Fitness Lanka', '2025-01-15', 'Good', 'Floor 1', 'available'),
('EQP002', 'Hammer Strength Rig', 'Hammer Strength', '2024-11-01', 'Good', 'Floor 2', 'available'),
('EQP003', 'Olympic Lifting Platform', 'Rogue Fitness', '2023-06-20', 'Needs Service', 'Floor 1', 'under_maintenance');
