-- =====================================================================
-- FitCore - viva demo data (adds to an existing database, never deletes)
--
-- Safe to run more than once: rows are matched on their codes / names and skipped if already present.
-- Dates are relative to CURDATE() so "Expiring Soon", "Expired", today's check-ins and the
-- 6-month revenue chart look right on whatever day this is imported.
--
-- Demo logins added here (test accounts for this local app only):
--   Member : sanduni@fitcore.lk / Member@123   (MEM003 - active Gold plan, bookings, attendance)
--   Trainer: ruwan@fitcore.lk   / Trainer@123  (TRN001 - classes, PT session, availability)
-- Payment slips referenced below are placeholder PDFs in assets/uploads/slips/demo_slip_*.pdf
-- =====================================================================
USE fitcore;

SET @admin := (SELECT user_id FROM users WHERE email = 'admin@fitcore.lk');

-- ---------------------------------------------------------------------
-- Login accounts
-- ---------------------------------------------------------------------
INSERT IGNORE INTO users (email, password_hash, role, status) VALUES
('sanduni@fitcore.lk', '$2y$10$TiXASSs1i1QOVrbL8m7Df.nNjW7XCmhjRP3q8IHOjXwAqBBoLTYG2', 'member', 'active'),
('ruwan@fitcore.lk',   '$2y$10$IY42mRhEvxgZmZG20j/Nge0iwU47fPrvNa/yry3JWneDUjrcpg6g6', 'trainer', 'active');

UPDATE trainers SET user_id = (SELECT user_id FROM users WHERE email = 'ruwan@fitcore.lk')
WHERE trainer_code = 'TRN001' AND user_id IS NULL;

-- ---------------------------------------------------------------------
-- Members (join dates spread over 6 months -> "new members" line on the dashboard chart)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO members (member_code, user_id, full_name, nic_passport, phone, address, email, join_date, account_status) VALUES
('MEM003', (SELECT user_id FROM users WHERE email = 'sanduni@fitcore.lk'),
           'Sanduni Perera',     '199823456789', '0771234567', '12 Galle Road, Colombo 03',      'sanduni@fitcore.lk',   CURDATE() - INTERVAL 4 MONTH,  'active'),
('MEM004', NULL, 'Kasun Nimalka',      '951234567V',   '0779876543', '45 Union Place, Colombo 02',     'kasun@example.com',    CURDATE() - INTERVAL 5 MONTH,  'active'),
('MEM005', NULL, 'Nadeesha Silva',     '199712345000', '0712223344', '8 Marine Drive, Colombo 06',     'nadeesha@example.com', CURDATE() - INTERVAL 3 MONTH,  'active'),
('MEM006', NULL, 'Tharindu Jayasuriya','200012304567', '0754567890', '22 Kandy Road, Kiribathgoda',    'tharindu@example.com', CURDATE() - INTERVAL 2 MONTH,  'active'),
('MEM007', NULL, 'Ishara Wickramasinghe','199645678912','0767788990','5 Flower Road, Colombo 07',      'ishara@example.com',   CURDATE() - INTERVAL 2 MONTH,  'active'),
('MEM008', NULL, 'Chamod Rajapaksha',  '981122334V',   '0703344556', '101 High Level Road, Nugegoda',  'chamod@example.com',   CURDATE() - INTERVAL 1 MONTH,  'active'),
('MEM009', NULL, 'Dilshan Gunawardena','199334455667', '0718899001', '3 Station Road, Dehiwala',       'dilshan@example.com',  CURDATE() - INTERVAL 5 MONTH,  'active'),
('MEM010', NULL, 'Piumi Herath',       '200156789012', '0776655443', '17 Temple Road, Maharagama',     'piumi@example.com',    CURDATE() - INTERVAL 10 DAY,   'active'),
('MEM011', NULL, 'Ravindu Senanayake', '199923456781', '0789012345', '60 Baseline Road, Colombo 09',   'ravindu@example.com',  CURDATE() - INTERVAL 3 DAY,    'active'),
('MEM012', NULL, 'Hiruni Abeykoon',    '200245678123', '0741237890', '9 Lake Drive, Battaramulla',     'hiruni@example.com',   CURDATE() - INTERVAL 5 DAY,    'active');

-- ---------------------------------------------------------------------
-- Subscriptions - one of every display state (BR-04 is computed from expiry_date)
--   MEM003 active, MEM004 / MEM005 expiring soon, MEM006 / MEM009 expired, others active
-- ---------------------------------------------------------------------
INSERT INTO subscriptions (member_id, plan_id, start_date, expiry_date, status)
SELECT m.member_id, p.plan_id, d.start_date, d.start_date + INTERVAL p.duration_months MONTH, 'active'
FROM (
          SELECT 'MEM003' code, 'Gold Pro Pass'   plan, CURDATE() - INTERVAL 4 MONTH                    start_date
    UNION SELECT 'MEM004', 'Silver Fitness',  CURDATE() - INTERVAL 3 MONTH + INTERVAL 4 DAY
    UNION SELECT 'MEM005', 'Monthly Starter', CURDATE() - INTERVAL 1 MONTH + INTERVAL 2 DAY
    UNION SELECT 'MEM006', 'Monthly Starter', CURDATE() - INTERVAL 1 MONTH - INTERVAL 10 DAY
    UNION SELECT 'MEM007', 'Platinum Elite',  CURDATE() - INTERVAL 2 MONTH
    UNION SELECT 'MEM008', 'Silver Fitness',  CURDATE() - INTERVAL 1 MONTH
    UNION SELECT 'MEM009', 'Gold Pro Pass',   CURDATE() - INTERVAL 7 MONTH
    UNION SELECT 'MEM010', 'Gold Pro Pass',   CURDATE() - INTERVAL 10 DAY
    UNION SELECT 'MEM012', 'Monthly Starter', CURDATE() - INTERVAL 5 DAY
) d
JOIN members m ON m.member_code = d.code
JOIN membership_plans p ON p.plan_name = d.plan
WHERE NOT EXISTS (SELECT 1 FROM subscriptions s WHERE s.member_id = m.member_id);

-- ---------------------------------------------------------------------
-- Payments: approved (linked to the subscription they paid for), pending, rejected
--   Pending ones are set up to show both BR-09 paths when approved in the viva:
--     PAY1010 MEM006 expired  -> approval restarts membership from today
--     PAY1011 MEM008 active   -> approval extends from current expiry date
--     PAY1012 MEM011 none yet -> approval creates the first subscription
-- ---------------------------------------------------------------------
INSERT IGNORE INTO payments (payment_code, member_id, plan_id, subscription_id, amount, transfer_date, reference_number,
                             slip_path, status, rejection_reason, verified_by, verified_at)
SELECT d.code, m.member_id, p.plan_id,
       IF(d.status = 'approved', (SELECT s.subscription_id FROM subscriptions s WHERE s.member_id = m.member_id AND s.plan_id = p.plan_id ORDER BY s.subscription_id LIMIT 1), NULL),
       COALESCE(d.amount, p.price), d.transfer_date, d.ref,
       CONCAT('assets/uploads/slips/demo_slip_', d.code, '.pdf'), d.status, d.reason,
       IF(d.status = 'pending', NULL, @admin),
       IF(d.status = 'pending', NULL, d.transfer_date + INTERVAL 1 DAY + INTERVAL 10 HOUR)
FROM (
          SELECT 'PAY1001' code, 'MEM003' member, 'Gold Pro Pass' plan, CURDATE() - INTERVAL 4 MONTH transfer_date, 'BOC-48213377' ref, 'approved' status, NULL amount, NULL reason
    UNION SELECT 'PAY1002', 'MEM004', 'Silver Fitness',  CURDATE() - INTERVAL 3 MONTH + INTERVAL 4 DAY,  'COM-55120984', 'approved', NULL, NULL
    UNION SELECT 'PAY1003', 'MEM005', 'Monthly Starter', CURDATE() - INTERVAL 1 MONTH + INTERVAL 2 DAY,  'SAM-77341209', 'approved', NULL, NULL
    UNION SELECT 'PAY1004', 'MEM006', 'Monthly Starter', CURDATE() - INTERVAL 1 MONTH - INTERVAL 10 DAY, 'HNB-30019876', 'approved', NULL, NULL
    UNION SELECT 'PAY1005', 'MEM007', 'Platinum Elite',  CURDATE() - INTERVAL 2 MONTH,                    'BOC-61200345', 'approved', NULL, NULL
    UNION SELECT 'PAY1006', 'MEM008', 'Silver Fitness',  CURDATE() - INTERVAL 1 MONTH,                    'NSB-90871234', 'approved', NULL, NULL
    UNION SELECT 'PAY1007', 'MEM009', 'Gold Pro Pass',   CURDATE() - INTERVAL 7 MONTH,                    'COM-11002233', 'approved', NULL, NULL
    UNION SELECT 'PAY1008', 'MEM010', 'Gold Pro Pass',   CURDATE() - INTERVAL 10 DAY,                     'SAM-45566778', 'approved', NULL, NULL
    UNION SELECT 'PAY1009', 'MEM012', 'Monthly Starter', CURDATE() - INTERVAL 5 DAY,                      'BOC-99887766', 'approved', NULL, NULL
    UNION SELECT 'PAY1010', 'MEM006', 'Monthly Starter', CURDATE() - INTERVAL 1 DAY,                      'HNB-30020511', 'pending',  NULL, NULL
    UNION SELECT 'PAY1011', 'MEM008', 'Gold Pro Pass',   CURDATE(),                                       'NSB-90875500', 'pending',  NULL, NULL
    UNION SELECT 'PAY1012', 'MEM011', 'Monthly Starter', CURDATE(),                                       'COM-55129001', 'pending',  NULL, NULL
    UNION SELECT 'PAY1013', 'MEM009', 'Gold Pro Pass',   CURDATE() - INTERVAL 6 DAY,                      'COM-11009999', 'rejected', 12000.00, 'Amount on the slip (LKR 12,000) does not match the Gold Pro Pass price.'
) d
JOIN members m ON m.member_code = d.member
JOIN membership_plans p ON p.plan_name = d.plan;

-- ---------------------------------------------------------------------
-- Classes (individual sessions, BR-22) + bookings (BR-13 capacity, BR-10 1-hour PT)
-- ---------------------------------------------------------------------
INSERT INTO classes (class_name, trainer_id, class_date, start_time, duration_minutes, capacity, status)
SELECT d.name, t.trainer_id, d.class_date, d.start_time, d.duration, d.capacity, d.status
FROM (
          SELECT 'Power CrossFit' name, 'TRN001' trainer, CURDATE() + INTERVAL 1 DAY class_date, '07:00:00' start_time, 60 duration, 20 capacity, 'scheduled' status
    UNION SELECT 'Morning HIIT Blast',   'TRN005', CURDATE() + INTERVAL 1 DAY, '06:00:00', 45, 15, 'scheduled'
    UNION SELECT 'Vinyasa Sunset Yoga',  'TRN007', CURDATE() + INTERVAL 1 DAY, '17:30:00', 60, 25, 'scheduled'
    UNION SELECT 'Hypertrophy Camp',     'TRN003', CURDATE() + INTERVAL 2 DAY, '18:30:00', 90, 12, 'scheduled'
    UNION SELECT 'Core & Mobility',      'TRN008', CURDATE() + INTERVAL 3 DAY, '08:00:00', 45, 3,  'scheduled'
    UNION SELECT 'Spin Express',         'TRN005', CURDATE() + INTERVAL 4 DAY, '19:00:00', 45, 18, 'scheduled'
    UNION SELECT 'Strength Fundamentals','TRN001', CURDATE() - INTERVAL 3 DAY, '07:00:00', 60, 20, 'completed'
) d
JOIN trainers t ON t.trainer_code = d.trainer
WHERE NOT EXISTS (SELECT 1 FROM classes c WHERE c.class_name = d.name);

-- Class bookings ("Core & Mobility" is filled to its capacity of 3 to demo the full-class rule).
INSERT INTO bookings (member_id, class_id, trainer_id, booking_type, slot_date, start_time, status)
SELECT m.member_id, c.class_id, NULL, 'class', c.class_date, c.start_time, d.status
FROM (
          SELECT 'MEM003' member, 'Power CrossFit' class_name, 'booked' status
    UNION SELECT 'MEM007', 'Power CrossFit',        'booked'
    UNION SELECT 'MEM010', 'Power CrossFit',        'booked'
    UNION SELECT 'MEM004', 'Vinyasa Sunset Yoga',   'booked'
    UNION SELECT 'MEM003', 'Vinyasa Sunset Yoga',   'cancelled'
    UNION SELECT 'MEM007', 'Hypertrophy Camp',      'booked'
    UNION SELECT 'MEM008', 'Hypertrophy Camp',      'booked'
    UNION SELECT 'MEM003', 'Core & Mobility',       'booked'
    UNION SELECT 'MEM005', 'Core & Mobility',       'booked'
    UNION SELECT 'MEM012', 'Core & Mobility',       'booked'
    UNION SELECT 'MEM003', 'Strength Fundamentals', 'completed'
    UNION SELECT 'MEM010', 'Strength Fundamentals', 'completed'
) d
JOIN members m ON m.member_code = d.member
JOIN classes c ON c.class_name = d.class_name
WHERE NOT EXISTS (SELECT 1 FROM bookings b WHERE b.member_id = m.member_id AND b.class_id = c.class_id);

-- One-hour personal-training sessions (BR-10).
INSERT INTO bookings (member_id, class_id, trainer_id, booking_type, slot_date, start_time, status)
SELECT m.member_id, NULL, t.trainer_id, 'pt', d.slot_date, d.start_time, 'booked'
FROM (
          SELECT 'MEM003' member, 'TRN001' trainer, CURDATE() + INTERVAL 2 DAY slot_date, '10:00:00' start_time
    UNION SELECT 'MEM007', 'TRN003', CURDATE() + INTERVAL 1 DAY, '15:00:00'
) d
JOIN members m ON m.member_code = d.member
JOIN trainers t ON t.trainer_code = d.trainer
WHERE NOT EXISTS (SELECT 1 FROM bookings b WHERE b.member_id = m.member_id AND b.trainer_id = t.trainer_id AND b.booking_type = 'pt');

-- Trainer availability (UC-06).
INSERT INTO trainer_availability (trainer_id, day_date, start_time, end_time)
SELECT t.trainer_id, d.day_date, d.start_time, d.end_time
FROM (
          SELECT 'TRN001' trainer, CURDATE() + INTERVAL 1 DAY day_date, '09:00:00' start_time, '13:00:00' end_time
    UNION SELECT 'TRN001', CURDATE() + INTERVAL 2 DAY, '09:00:00', '12:00:00'
    UNION SELECT 'TRN003', CURDATE() + INTERVAL 1 DAY, '14:00:00', '18:00:00'
    UNION SELECT 'TRN005', CURDATE() + INTERVAL 2 DAY, '06:00:00', '10:00:00'
    UNION SELECT 'TRN007', CURDATE() + INTERVAL 3 DAY, '16:00:00', '20:00:00'
) d
JOIN trainers t ON t.trainer_code = d.trainer
WHERE NOT EXISTS (SELECT 1 FROM trainer_availability a WHERE a.trainer_id = t.trainer_id AND a.day_date = d.day_date);

-- ---------------------------------------------------------------------
-- Member attendance (BR-15: member + date + check-in only). Unique per member per day.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO member_attendance (member_id, attendance_date, check_in_time, recorded_by)
SELECT m.member_id, CURDATE() - INTERVAL d.days_ago DAY, d.t, @admin
FROM (
          SELECT 'MEM003' member, 0 days_ago, '06:42:00' t
    UNION SELECT 'MEM007', 0, '07:15:00'
    UNION SELECT 'MEM010', 0, '08:05:00'
    UNION SELECT 'MEM004', 0, '17:20:00'
    UNION SELECT 'MEM003', 1, '06:50:00'
    UNION SELECT 'MEM005', 1, '18:10:00'
    UNION SELECT 'MEM008', 1, '19:02:00'
    UNION SELECT 'MEM012', 1, '07:30:00'
    UNION SELECT 'MEM003', 2, '06:45:00'
    UNION SELECT 'MEM007', 2, '07:05:00'
    UNION SELECT 'MEM010', 3, '08:15:00'
    UNION SELECT 'MEM003', 4, '06:40:00'
    UNION SELECT 'MEM004', 4, '17:45:00'
    UNION SELECT 'MEM008', 5, '19:10:00'
    UNION SELECT 'MEM003', 6, '06:55:00'
) d
JOIN members m ON m.member_code = d.member;

-- ---------------------------------------------------------------------
-- Employees, staff attendance and leave (BR-03 / BR-17: no login accounts)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO employees (employee_code, full_name, phone, job_role, shift, status) VALUES
('EMP005', 'Dilani Rathnayake', '0765556677', 'receptionist', 'general', 'active'),
('EMP006', 'Nimal Fernando',    '0761112233', 'cleaner',      'morning', 'active'),
('EMP007', 'Kamal Perera',      '0772345678', 'maintenance',  'evening', 'active'),
('EMP008', 'Sachini Madushani', '0714567812', 'receptionist', 'evening', 'on_leave');

INSERT IGNORE INTO employee_attendance (employee_id, attendance_date, check_in_time, check_out_time)
SELECT e.employee_id, CURDATE() - INTERVAL d.days_ago DAY, d.cin, d.cout
FROM (
          SELECT 'EMP005' emp, 0 days_ago, '08:00:00' cin, NULL cout
    UNION SELECT 'EMP006', 0, '05:30:00', '13:30:00'
    UNION SELECT 'EMP005', 1, '08:05:00', '17:00:00'
    UNION SELECT 'EMP006', 1, '05:35:00', '13:40:00'
    UNION SELECT 'EMP007', 1, '14:00:00', '22:10:00'
) d
JOIN employees e ON e.employee_code = d.emp;

INSERT INTO employee_leave (employee_id, leave_type, start_date, end_date, reason, status)
SELECT e.employee_id, d.leave_type, d.start_date, d.end_date, d.reason, d.status
FROM (
          SELECT 'EMP008' emp, 'medical' leave_type, CURDATE() - INTERVAL 1 DAY start_date, CURDATE() + INTERVAL 2 DAY end_date, 'Fever - doctor advised rest' reason, 'approved' status
    UNION SELECT 'EMP007', 'casual', CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 6 DAY, 'Family function in Kandy', 'pending'
) d
JOIN employees e ON e.employee_code = d.emp
WHERE NOT EXISTS (SELECT 1 FROM employee_leave l WHERE l.employee_id = e.employee_id);

-- ---------------------------------------------------------------------
-- Equipment + maintenance history (BR-18 statuses, BR-19 major equipment only)
-- ---------------------------------------------------------------------
INSERT IGNORE INTO equipment (equipment_code, equipment_name, supplier, purchase_date, condition_status, location, status, notes) VALUES
('EQP006', 'Matrix Treadmill T50',       'Matrix Fitness Lanka', '2025-01-15', 'Good',          'Floor 1', 'available',      NULL),
('EQP007', 'Concept2 Rowing Machine',    'Rogue Fitness',        '2025-03-02', 'Good',          'Floor 1', 'in_use',         NULL),
('EQP008', 'Life Fitness Cable Crossover','Life Fitness',        '2023-09-10', 'Cable frayed',  'Floor 2', 'out_of_service', 'Left cable frayed - replacement ordered'),
('EQP009', 'Technogym Spin Bike',        'Technogym',            '2024-07-21', 'Good',          'Studio',  'available',      NULL);

INSERT INTO equipment_maintenance (equipment_id, maintenance_date, next_due_date, notes)
SELECT q.equipment_id, d.mdate, d.due, d.notes
FROM (
          SELECT 'EQP003' code, CURDATE() - INTERVAL 2 DAY mdate, CURDATE() + INTERVAL 5 DAY due, 'Platform boards loose - re-fixed, re-check next week' notes
    UNION SELECT 'EQP006', CURDATE() - INTERVAL 1 MONTH, CURDATE() + INTERVAL 2 MONTH, 'Belt lubricated and tension adjusted'
    UNION SELECT 'EQP002', CURDATE() - INTERVAL 2 MONTH, CURDATE() + INTERVAL 1 MONTH, 'Bolts tightened, pulleys cleaned'
    UNION SELECT 'EQP008', CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 3 DAY, 'Cable inspection - replacement part ordered'
) d
JOIN equipment q ON q.equipment_code = d.code
WHERE NOT EXISTS (SELECT 1 FROM equipment_maintenance x WHERE x.equipment_id = q.equipment_id AND x.notes = d.notes);

-- ---------------------------------------------------------------------
-- Visitor enquiries (FR-02) + admin notifications
-- ---------------------------------------------------------------------
INSERT INTO enquiries (name, phone, email, message, status, created_at)
SELECT d.name, d.phone, d.email, d.message, d.status, d.created_at
FROM (
          SELECT 'Amaya Fonseka' name, '0771239876' phone, 'amaya@example.com' email, 'Do you have ladies-only yoga sessions in the evening?' message, 'new' status, NOW() - INTERVAL 3 HOUR created_at
    UNION SELECT 'Roshan Peiris', '0705554433', 'roshan@example.com', 'What is the price of the 12-month plan for students?', 'reviewed', NOW() - INTERVAL 2 DAY
    UNION SELECT 'Nuwan Ekanayake', '0719988776', NULL, 'Can I come for a free trial session this weekend?', 'closed', NOW() - INTERVAL 6 DAY
) d
WHERE NOT EXISTS (SELECT 1 FROM enquiries e WHERE e.name = d.name);

INSERT INTO notifications (type, message, link, target_role, is_read, created_at)
SELECT d.type, d.message, d.link, 'admin', d.is_read, d.created_at
FROM (
          SELECT 'payment' type, 'New payment PAY1012 submitted by Ravindu Senanayake - needs verification.' message, 'modules/payments/index.php' link, 0 is_read, NOW() - INTERVAL 20 MINUTE created_at
    UNION SELECT 'payment', 'New payment PAY1011 submitted by Chamod Rajapaksha - needs verification.', 'modules/payments/index.php', 0, NOW() - INTERVAL 1 HOUR
    UNION SELECT 'enquiry', 'New enquiry from Amaya Fonseka.', 'modules/enquiries/index.php', 0, NOW() - INTERVAL 3 HOUR
    UNION SELECT 'leave', 'Leave request from Kamal Perera (casual) - needs approval.', 'modules/attendance/index.php', 0, NOW() - INTERVAL 5 HOUR
    UNION SELECT 'payment', 'New payment PAY1010 submitted by Tharindu Jayasuriya - needs verification.', 'modules/payments/index.php', 1, NOW() - INTERVAL 1 DAY
    UNION SELECT 'equipment', 'Life Fitness Cable Crossover marked Out of Service.', 'modules/equipment/index.php', 1, NOW() - INTERVAL 1 DAY
) d
WHERE NOT EXISTS (SELECT 1 FROM notifications n WHERE n.message = d.message);
