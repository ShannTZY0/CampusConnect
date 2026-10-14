-- =====================================================================
-- CampusConnect Database Schema
-- A capstone/thesis project management system
-- =====================================================================

CREATE DATABASE IF NOT EXISTS campusconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusconnect;

-- ---------------------------------------------------------------------
-- ROLES
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE  -- student, adviser, coordinator, admin
);

INSERT INTO roles (role_name) VALUES ('student'), ('adviser'), ('coordinator'), ('admin');

-- ---------------------------------------------------------------------
-- USERS
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    id_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    program VARCHAR(150) DEFAULT NULL,
    department VARCHAR(150) DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT 'default-profile.png',
    contact_number VARCHAR(30) DEFAULT NULL,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    remember_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- ---------------------------------------------------------------------
-- GROUPS (student project groups)
-- ---------------------------------------------------------------------
CREATE TABLE `groups` (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(150) NOT NULL,
    group_code VARCHAR(20) UNIQUE,
    leader_id INT NOT NULL,
    adviser_id INT DEFAULT NULL,
    status ENUM('forming','active','disbanded') DEFAULT 'forming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES users(user_id),
    FOREIGN KEY (adviser_id) REFERENCES users(user_id)
);

CREATE TABLE group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role_in_group ENUM('leader','member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_group_user (group_id, user_id)
);

-- ---------------------------------------------------------------------
-- ADVISER PREFERENCES / ASSIGNMENTS
-- ---------------------------------------------------------------------
CREATE TABLE adviser_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    adviser_id INT NOT NULL,
    preference_rank TINYINT DEFAULT 1,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- PROPOSALS
-- ---------------------------------------------------------------------
CREATE TABLE proposals (
    proposal_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    abstract TEXT,
    file_path VARCHAR(255) DEFAULT NULL,
    status ENUM('draft','submitted','under_review','approved','revision','rejected') DEFAULT 'draft',
    submitted_at DATETIME DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    review_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- PROJECTS
-- ---------------------------------------------------------------------
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    proposal_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100) DEFAULT NULL,
    status ENUM('proposed','in_progress','for_evaluation','completed','dropped') DEFAULT 'proposed',
    progress_percent TINYINT DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (proposal_id) REFERENCES proposals(proposal_id)
);

CREATE TABLE project_milestones (
    milestone_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE DEFAULT NULL,
    status ENUM('pending','in_progress','completed','overdue') DEFAULT 'pending',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);

CREATE TABLE project_progress_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    update_text TEXT NOT NULL,
    progress_percent TINYINT DEFAULT 0,
    logged_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- CONSULTATIONS
-- ---------------------------------------------------------------------
CREATE TABLE consultations (
    consultation_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    adviser_id INT NOT NULL,
    requested_by INT NOT NULL,
    preferred_date DATETIME DEFAULT NULL,
    scheduled_date DATETIME DEFAULT NULL,
    mode ENUM('face_to_face','online') DEFAULT 'face_to_face',
    agenda TEXT,
    notes TEXT,
    status ENUM('pending','approved','rescheduled','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (adviser_id) REFERENCES users(user_id),
    FOREIGN KEY (requested_by) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- SUBMISSIONS
-- ---------------------------------------------------------------------
CREATE TABLE submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    submitted_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    submission_type VARCHAR(100) DEFAULT 'document',
    status ENUM('pending','reviewed','approved','returned') DEFAULT 'pending',
    remarks TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- EVALUATIONS
-- ---------------------------------------------------------------------
CREATE TABLE evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    evaluation_type VARCHAR(100) DEFAULT 'final',
    score DECIMAL(5,2) DEFAULT NULL,
    criteria_scores JSON DEFAULT NULL,
    comments TEXT,
    file_path VARCHAR(255) DEFAULT NULL,
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- FEEDBACK (adviser feedback to groups)
-- ---------------------------------------------------------------------
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    adviser_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (adviser_id) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- ANNOUNCEMENTS
-- ---------------------------------------------------------------------
CREATE TABLE announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    posted_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('all','students','advisers','coordinators') DEFAULT 'all',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id)
);

-- ---------------------------------------------------------------------
-- NOTIFICATIONS
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- ACTIVITY LOGS (admin)
-- ---------------------------------------------------------------------
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------
-- SYSTEM SETTINGS (admin)
-- ---------------------------------------------------------------------
CREATE TABLE system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO system_settings (setting_key, setting_value) VALUES
('site_name', 'CampusConnect'),
('school_year', '2026-2027'),
('proposal_submission_open', '1'),
('max_group_size', '5');
