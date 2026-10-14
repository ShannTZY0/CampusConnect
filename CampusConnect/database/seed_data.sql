USE campusconnect;

-- Default password for ALL seeded accounts below is: Password123
-- (hash generated with PHP password_hash / bcrypt)
SET @pw = '$2y$10$WqQ8B4C9r0j1w2y3xZ1qUe1234567890abcdefghijklmnopqrstuv';
-- NOTE: run database/README instructions to regenerate a real hash with
-- php -r "echo password_hash('Password123', PASSWORD_DEFAULT);"
-- and replace @pw below before importing, since this placeholder hash
-- will not validate against the real algorithm output.

INSERT INTO users (role_id, id_number, first_name, last_name, email, password_hash, program, department) VALUES
(4, 'ADM-0001', 'System', 'Administrator', 'admin@campusconnect.edu', @pw, NULL, 'IT Office'),
(3, 'CRD-0001', 'Maria', 'Santos', 'coordinator@campusconnect.edu', @pw, NULL, 'College of Computing'),
(2, 'ADV-0001', 'John', 'Dela Cruz', 'adviser@campusconnect.edu', @pw, NULL, 'College of Computing'),
(1, 'STU-0001', 'Anna', 'Reyes', 'anna.reyes@campusconnect.edu', @pw, 'BS Computer Science', 'College of Computing'),
(1, 'STU-0002', 'Mark', 'Lopez', 'mark.lopez@campusconnect.edu', @pw, 'BS Computer Science', 'College of Computing');

INSERT INTO `groups` (group_name, group_code, leader_id, adviser_id, status) VALUES
('Group Alpha', 'GRP-2026-001', 4, 3, 'active');

INSERT INTO group_members (group_id, user_id, role_in_group) VALUES
(1, 4, 'leader'),
(1, 5, 'member');

INSERT INTO proposals (group_id, title, abstract, status) VALUES
(1, 'CampusConnect: A Web-Based Capstone Management System',
 'A system to streamline capstone/thesis coordination between students, advisers, and coordinators.',
 'approved');

INSERT INTO projects (group_id, proposal_id, title, description, status, progress_percent) VALUES
(1, 1, 'CampusConnect: A Web-Based Capstone Management System',
 'Development of the CampusConnect platform.', 'in_progress', 40);

INSERT INTO announcements (posted_by, title, body, audience) VALUES
(2, 'Welcome to CampusConnect', 'The proposal submission window for AY 2026-2027 is now open.', 'all');
