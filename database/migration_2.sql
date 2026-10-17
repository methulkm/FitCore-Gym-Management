-- Run this ONCE if your `fitcore` database already exists from before (i.e. you don't want to
-- re-import schema.sql and lose your test data). Adds: admin password-recovery fields + notifications table.
USE fitcore;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS security_question    VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS security_answer_hash VARCHAR(255) NULL;

-- Sets the admin's recovery question/answer only if it hasn't been set yet.
-- Answer is 'colombo' (case-insensitive - the app lowercases before checking).
UPDATE users
SET security_question = 'Which city is FitCore gym located in?',
    security_answer_hash = '$2y$10$GD1VyiuGlEPblQhGqmE3meO813Pa9gjo09UvnBd9WJXJx7kI/g3y2'
WHERE email = 'admin@fitcore.lk' AND security_answer_hash IS NULL;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    type             VARCHAR(40) NOT NULL,
    message          VARCHAR(255) NOT NULL,
    link             VARCHAR(255) NULL,
    target_role      ENUM('admin') NOT NULL DEFAULT 'admin',
    is_read          TINYINT(1) NOT NULL DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
