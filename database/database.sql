DROP DATABASE IF EXISTS miso_corp;
CREATE DATABASE miso_corp;
USE miso_corp;

-- 1. TABEL DEPARTMENTS
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    note TEXT
);

-- 2. TABEL REQUIREMENTS (ATURAN MAIN)
CREATE TABLE requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    education VARCHAR(50),
    experience VARCHAR(50),
    status_degree_certificate ENUM('Required','Optional','None') DEFAULT 'None', -- Ijazah
    status_identity_card ENUM('Required','Optional','None') DEFAULT 'Required',  -- KTP (Default Wajib)
    status_family_register ENUM('Required','Optional','None') DEFAULT 'None',    -- KK
    status_police_certificate ENUM('Required','Optional','None') DEFAULT 'None', -- SKCK
    status_passport_photo ENUM('Required','Optional','None') DEFAULT 'None',     -- Pas Foto
    status_cv ENUM('Required','Optional','None') DEFAULT 'Required',             -- CV (Default Wajib)
    status_resume ENUM('Required','Optional','None') DEFAULT 'None',             -- Resume
    status_training_certificate ENUM('Required','Optional','None') DEFAULT 'None',
    status_portfolio ENUM('Required','Optional','None') DEFAULT 'None',          -- Portofolio
    status_health_certificate ENUM('Required','Optional','None') DEFAULT 'None'
);

-- 3. TABEL JOBS
CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    title VARCHAR(50), 
    description TEXT,
    requirement_id INT, 
    job_type ENUM('On-Site','Remote','Hybrid'),
    salary INT,
    quota INT,
    closing_date DATE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (requirement_id) REFERENCES requirements(requirement_id)
);

-- 4. TABEL USER
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(50),
    username VARCHAR(50),
    role ENUM('admin','interviewer','candidate') DEFAULT 'candidate',
    email VARCHAR(30),
    phone_number VARCHAR(15),
    password VARCHAR(255) 
);

-- 5. TABEL APPLICATIONS (TEMPAT SIMPAN FILE)
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    user_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1,
    file_degree_certificate VARCHAR(255),
    file_identity_card VARCHAR(255),
    file_family_register VARCHAR(255),
    file_police_certificate VARCHAR(255),
    file_passport_photo VARCHAR(255),
    file_cv VARCHAR(255),
    file_resume VARCHAR(255),
    file_training_certificate VARCHAR(255),
    file_portfolio VARCHAR(255),
    file_health_certificate VARCHAR(255),
    
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- 6. TABEL SCORING
CREATE TABLE scoring (
    scoring_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    application_id INT,
    interview_score INT,
    technical_score INT,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
);

-- 1. Departments
INSERT INTO departments (name, note) VALUES 
('Engineering', 'Bertanggung jawab membangun dan memelihara sistem aplikasi'),
('Marketing', 'Merancang kampanye promosi dan strategi branding'),
('Sales', 'Mencapai target pendapatan dan akuisisi klien baru'),
('HR', 'Mengelola rekrutmen, payroll, dan kesejahteraan karyawan'),
('Design', 'Membuat konsep visual dan antarmuka pengguna');

-- 2. Requirements 
-- 1. Insert untuk IT (Butuh Portfolio)
INSERT INTO requirements (education, experience, status_cv, status_identity_card, status_portfolio) 
VALUES ('S1/S2', 'Min. 1-2 Tahun di Backend/Fullstack', 'Required', 'Required', 'Optional');
INSERT INTO requirements (education, experience, status_cv, status_identity_card, status_police_certificate, status_family_register) 
VALUES ('S1/SMA/SMK', 'Min. 1 Tahun di General HR', 'Required', 'Required', 'Required', 'Optional');
INSERT INTO requirements (education, experience, status_cv, status_identity_card, status_passport_photo) 
VALUES ('SMA/SMK', 'Min. 1-2 Tahun di Sales & Marketing', 'Required', 'Required', 'Required');
INSERT INTO requirements (education, experience, status_cv, status_identity_card, status_portfolio) 
VALUES ('S1/S2/SMA/SMK', 'Min. 1-4 Tahun di Industri Kreatif/Agency', 'Required', 'Required', 'Required');


-- 3. Jobs (Data Asli Anda)
INSERT INTO jobs (department_id, title, description, requirement_id, job_type, salary, quota, closing_date) VALUES 
(1, 'IT Development', 'Mengembangkan dan memelihara sistem backend perusahaan', 1, 'On-Site', 15000000, 20, '2025-12-31'),
(2, 'Staff Marketing', 'Merancang strategi kampanye dan promosi produk', 3, 'On-Site', 10000000, 100, '2025-12-31'),
(4, 'Staff HRD', 'Mengelola administrasi karyawan dan proses rekrutmen', 2, 'On-Site', 7000000, 50, '2025-12-31'),
(5, 'Designer', 'Membuat aset visual, UI/UX, dan materi kreatif', 4, 'On-Site', 20000000, 500, '2025-12-31');


-- 4. User (Data Asli Anda)
-- Password diset ke hash "123456" agar bisa login
INSERT INTO user (full_name, username, role, email, phone_number, password) VALUES 
('Elsya Nur Aulia Handayani', 'elsya_admin', 'admin', 'elsya@company.com', '081211112222', '$2y$10$EpRnTzVlqHNP0r.pbKtTHOny.k.mpsv/K4y.ZgC/h.h.w.h.w.h.'), -- Password: 123456
('Falih Dzakwan', 'falih_int', 'interviewer', 'falih@company.com', '081233334444', '$2y$10$EpRnTzVlqHNP0r.pbKtTHOny.k.mpsv/K4y.ZgC/h.h.w.h.w.h.'), -- Password: 123456
('Oktavia Nur Rahmadani', 'oktvia', 'candidate', 'oktavia@gmail.com', '081255556666', '$2y$10$EpRnTzVlqHNP0r.pbKtTHOny.k.mpsv/K4y.ZgC/h.h.w.h.w.h.'), -- Password: 123456
('Laudya Aprilia Khoirum', 'lauuudy_a', 'candidate', 'laudya@gmail.com', '081277778888', '$2y$10$EpRnTzVlqHNP0r.pbKtTHOny.k.mpsv/K4y.ZgC/h.h.w.h.w.h.'); -- Password: 123456


-- 5. Applications (Data Asli Anda)
INSERT INTO applications (job_id, user_id, created_at, is_active) VALUES 
(1, 3, '2024-10-01 09:00:00', 1), -- Oktavia apply IT
(2, 4, '2024-10-02 10:30:00', 1), -- Laudya apply Marketing
(3, 3, '2024-10-03 15:00:00', 1), -- Oktavia apply HRD
(3, 4, '2024-10-04 08:00:00', 0); -- Laudya apply HRD (Inactive)


-- 6. Scoring (Data Asli Anda)
INSERT INTO scoring (user_id, application_id, interview_score, technical_score) VALUES 
(2, 1, 85, 90), -- Falih nilai Oktavia
(2, 2, 88, 82), -- Falih nilai Laudya
(2, 3, 70, 70), -- Falih nilai Oktavia (HRD)
(2, 4, 0, 0);    -- Falih nilai Laudya (HRD)

-- ==========================================================
-- NOTE PENTING:
-- Password untuk semua user di atas adalah: 123456
-- Jika ingin login sebagai Elsya (Admin), gunakan pass: 123456
-- ==========================================================