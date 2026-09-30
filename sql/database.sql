-- ============================================================
-- Ashabesuffa Foundation - Complete Database Schema
-- Engine: MySQL 5.7+ / MariaDB 10.3+
-- Charset: utf8mb4 (full Unicode, Arabic + Urdu support)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS ashabesuffa_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ashabesuffa_db;

-- ------------------------------------------------------------
-- Users (admin + staff)
-- ------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  role ENUM('admin','editor','author') DEFAULT 'editor',
  is_active TINYINT(1) DEFAULT 1,
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin, password=Admin@123
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@ashabesuffa.org',
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBvz5Z5Z5Z5Z5Z5Z',
 'Administrator', 'admin');

-- ------------------------------------------------------------
-- Settings (site-wide key/value)
-- ------------------------------------------------------------
CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name_en', 'Ashabesuffa Foundation'),
('site_name_ur', 'اصحابِ صفہ فاؤنڈیشن'),
('site_name_ar', 'مؤسسة أصحاب الصفة'),
('site_tagline_en', 'Knowledge • Faith • Service'),
('site_tagline_ur', 'علم • ایمان • خدمت'),
('site_tagline_ar', 'علم • إيمان • خدمة'),
('contact_phone', '+92 300 1234567'),
('contact_email', 'info@ashabesuffa.org'),
('contact_address_en', '123 Main Road, Karachi, Pakistan'),
('contact_address_ur', '۱۲۳ مین روڈ، کراچی، پاکستان'),
('contact_address_ar', '١٢٣ الطريق الرئيسي، كراتشي، باكستان'),
('facebook_url', 'https://facebook.com/'),
('youtube_url', 'https://youtube.com/'),
('twitter_url', 'https://twitter.com/'),
('whatsapp_number', '923001234567'),
('default_language', 'en'),
('items_per_page', '10');

-- ------------------------------------------------------------
-- News / Articles
-- ------------------------------------------------------------
CREATE TABLE news (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) NOT NULL UNIQUE,
  title_en VARCHAR(255) NOT NULL,
  title_ur VARCHAR(255) DEFAULT NULL,
  title_ar VARCHAR(255) DEFAULT NULL,
  excerpt_en TEXT,
  excerpt_ur TEXT,
  excerpt_ar TEXT,
  content_en LONGTEXT,
  content_ur LONGTEXT,
  content_ar LONGTEXT,
  featured_image VARCHAR(255) DEFAULT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  author_id INT UNSIGNED DEFAULT NULL,
  status ENUM('draft','published','archived') DEFAULT 'draft',
  views INT UNSIGNED DEFAULT 0,
  published_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status_pub (status, published_at),
  INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- News Categories
-- ------------------------------------------------------------
CREATE TABLE news_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  name_en VARCHAR(100) NOT NULL,
  name_ur VARCHAR(100) DEFAULT NULL,
  name_ar VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO news_categories (slug, name_en, name_ur, name_ar) VALUES
('announcements', 'Announcements', 'اعلانات', 'إعلانات'),
('events', 'Events', 'تقریبات', 'فعاليات'),
('academics', 'Academics', 'تعلیمی', 'أكاديمي'),
('welfare', 'Welfare', 'فلاح', 'رعاية');

-- ------------------------------------------------------------
-- Magazine (Bayyinat-style)
-- ------------------------------------------------------------
CREATE TABLE magazine_issues (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_number INT UNSIGNED NOT NULL,
  issue_year INT UNSIGNED NOT NULL,
  issue_month TINYINT UNSIGNED NOT NULL,
  title_en VARCHAR(200),
  title_ur VARCHAR(200),
  title_ar VARCHAR(200),
  cover_image VARCHAR(255),
  pdf_file VARCHAR(255),
  description_en TEXT,
  description_ur TEXT,
  description_ar TEXT,
  is_published TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_issue (issue_number, issue_year),
  INDEX idx_year_month (issue_year, issue_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Magazine Articles
-- ------------------------------------------------------------
CREATE TABLE magazine_articles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id INT UNSIGNED NOT NULL,
  slug VARCHAR(200) NOT NULL,
  title_en VARCHAR(255),
  title_ur VARCHAR(255),
  title_ar VARCHAR(255),
  author_name VARCHAR(120),
  content_en LONGTEXT,
  content_ur LONGTEXT,
  content_ar LONGTEXT,
  page_number INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (issue_id) REFERENCES magazine_issues(id) ON DELETE CASCADE,
  INDEX idx_issue (issue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Q&A (Questions & Answers - Shariah rulings)
-- ------------------------------------------------------------
CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  questioner_name VARCHAR(120),
  questioner_email VARCHAR(160),
  questioner_phone VARCHAR(30),
  question_en TEXT NOT NULL,
  question_ur TEXT,
  question_ar TEXT,
  answer_en LONGTEXT,
  answer_ur LONGTEXT,
  answer_ar LONGTEXT,
  category_id INT UNSIGNED DEFAULT NULL,
  answered_by INT UNSIGNED DEFAULT NULL,
  status ENUM('pending','answered','rejected') DEFAULT 'pending',
  is_public TINYINT(1) DEFAULT 0,
  views INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  answered_at DATETIME NULL,
  INDEX idx_status (status),
  INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE question_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  name_en VARCHAR(100),
  name_ur VARCHAR(100),
  name_ar VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO question_categories (slug, name_en, name_ur, name_ar) VALUES
('aqeedah', 'Aqeedah (Belief)', 'عقیدہ', 'العقيدة'),
('fiqh', 'Fiqh (Jurisprudence)', 'فقہ', 'الفقه'),
('hadith', 'Hadith', 'حدیث', 'الحديث'),
('tafsir', 'Tafsir', 'تفسیر', 'التفسير'),
('family', 'Family & Marriage', 'خاندان و نکاح', 'الأسرة والزواج'),
('finance', 'Finance & Business', 'مالیات و کاروبار', 'المال والأعمال'),
('worship', 'Worship', 'عبادات', 'العبادات'),
('general', 'General', 'عمومی', 'عام');

-- ------------------------------------------------------------
-- Departments
-- ------------------------------------------------------------
CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name_en VARCHAR(160) NOT NULL,
  name_ur VARCHAR(160),
  name_ar VARCHAR(160),
  description_en LONGTEXT,
  description_ur LONGTEXT,
  description_ar LONGTEXT,
  icon VARCHAR(80),
  image VARCHAR(255),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO departments (slug, name_en, name_ur, name_ar, icon, sort_order) VALUES
('hifz-tajweed', 'Hifz & Tajweed', 'حفظ و تجوید', 'التحفيظ والتجويد', 'book', 1),
('dars-nizami', 'Dars-e-Nizami', 'درس نظامی', 'درس نظامي', 'mosque', 2),
('primary-education', 'Primary Education', 'ابتدائی تعلیم', 'التعليم الابتدائي', 'cap', 3),
('welfare-dawah', 'Welfare & Dawah', 'فلاح و تبلیغ', 'الرعاية والدعوة', 'handshake', 4),
('higher-studies', 'Higher Studies', 'اعلیٰ تعلیم', 'الدراسات العليا', 'graduate', 5),
('arabic-institute', 'Arabic Institute', 'عربی انسٹیٹیوٹ', 'معهد اللغة العربية', 'globe', 6);

-- ------------------------------------------------------------
-- Gallery
-- ------------------------------------------------------------
CREATE TABLE gallery_albums (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  title_en VARCHAR(160),
  title_ur VARCHAR(160),
  title_ar VARCHAR(160),
  description_en TEXT,
  description_ur TEXT,
  description_ar TEXT,
  cover_image VARCHAR(255),
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gallery_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  album_id INT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  caption_en VARCHAR(255),
  caption_ur VARCHAR(255),
  caption_ar VARCHAR(255),
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (album_id) REFERENCES gallery_albums(id) ON DELETE CASCADE,
  INDEX idx_album (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Admissions
-- ------------------------------------------------------------
CREATE TABLE admission_programs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name_en VARCHAR(160) NOT NULL,
  name_ur VARCHAR(160),
  name_ar VARCHAR(160),
  description_en LONGTEXT,
  description_ur LONGTEXT,
  description_ar LONGTEXT,
  duration VARCHAR(80),
  eligibility_en TEXT,
  eligibility_ur TEXT,
  eligibility_ar TEXT,
  fee VARCHAR(80),
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admission_applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_no VARCHAR(30) NOT NULL UNIQUE,
  program_id INT UNSIGNED NOT NULL,
  student_name VARCHAR(160) NOT NULL,
  father_name VARCHAR(160) NOT NULL,
  date_of_birth DATE,
  cnic VARCHAR(20),
  phone VARCHAR(30),
  email VARCHAR(160),
  address TEXT,
  previous_school VARCHAR(200),
  previous_grade VARCHAR(40),
  message TEXT,
  status ENUM('pending','reviewing','approved','rejected','enrolled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (program_id) REFERENCES admission_programs(id),
  INDEX idx_status (status),
  INDEX idx_app_no (application_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Contact Messages
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(30),
  subject VARCHAR(200),
  department VARCHAR(80),
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Donations
-- ------------------------------------------------------------
CREATE TABLE donations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donor_name VARCHAR(160),
  donor_email VARCHAR(160),
  donor_phone VARCHAR(30),
  amount DECIMAL(12,2) NOT NULL,
  currency VARCHAR(8) DEFAULT 'PKR',
  purpose VARCHAR(160),
  payment_method VARCHAR(60),
  transaction_id VARCHAR(120),
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  is_anonymous TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Prayer Times (cached per city/date)
-- ------------------------------------------------------------
CREATE TABLE prayer_times (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(120) NOT NULL,
  country VARCHAR(80) NOT NULL,
  prayer_date DATE NOT NULL,
  fajr TIME,
  sunrise TIME,
  dhuhr TIME,
  asr TIME,
  maghrib TIME,
  isha TIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_city_date (city, country, prayer_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Newsletter Subscribers
-- ------------------------------------------------------------
CREATE TABLE subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160) NOT NULL UNIQUE,
  name VARCHAR(120),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Activity Log
-- ------------------------------------------------------------
CREATE TABLE activity_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED,
  action VARCHAR(80) NOT NULL,
  entity VARCHAR(80),
  entity_id INT UNSIGNED,
  details TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
