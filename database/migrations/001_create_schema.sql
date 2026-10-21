-- Persomy : schéma initial (MySQL 5.7+/8 ou MariaDB 10.4+), utf8mb4.
-- Exécuter via `php bin/migrate.php` ou importer ce fichier dans phpMyAdmin.

CREATE TABLE IF NOT EXISTS migrations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(190) NOT NULL,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_migrations_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  name VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_categories_slug (slug),
  KEY idx_project_categories_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_category_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  name VARCHAR(150) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_category_locale (category_id, locale),
  CONSTRAINT fk_cat_tr_category FOREIGN KEY (category_id) REFERENCES project_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  category_id INT UNSIGNED NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  is_demo TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  main_image VARCHAR(80) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_projects_slug (slug),
  KEY idx_projects_listing (status, sort_order, published_at),
  KEY idx_projects_category (category_id),
  CONSTRAINT fk_projects_category FOREIGN KEY (category_id) REFERENCES project_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  title VARCHAR(200) NOT NULL DEFAULT '',
  summary VARCHAR(500) NOT NULL DEFAULT '',
  description TEXT NULL,
  problem TEXT NULL,
  solution TEXT NULL,
  results TEXT NULL,
  image_alt VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_locale (project_id, locale),
  CONSTRAINT fk_project_tr_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(60) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  title VARCHAR(200) NOT NULL DEFAULT '',
  meta_title VARCHAR(200) NOT NULL DEFAULT '',
  meta_description VARCHAR(320) NOT NULL DEFAULT '',
  intro TEXT NULL,
  content MEDIUMTEXT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_page_locale (page_id, locale),
  CONSTRAINT fk_page_tr_page FOREIGN KEY (page_id) REFERENCES pages (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  icon VARCHAR(30) NOT NULL DEFAULT 'code',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_services_slug (slug),
  KEY idx_services_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  service_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  title VARCHAR(200) NOT NULL DEFAULT '',
  summary VARCHAR(600) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_locale (service_id, locale),
  CONSTRAINT fk_service_tr_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_submissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  company VARCHAR(150) NOT NULL DEFAULT '',
  subject VARCHAR(200) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  locale VARCHAR(5) NOT NULL DEFAULT 'fr',
  consent TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('new','in_progress','done','spam') NOT NULL DEFAULT 'new',
  mail_status ENUM('sent','failed','skipped') NOT NULL DEFAULT 'skipped',
  ip_hash CHAR(64) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_submissions_status (status, created_at),
  KEY idx_submissions_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  bucket CHAR(64) NOT NULL,
  hits INT UNSIGNED NOT NULL DEFAULT 0,
  window_start INT UNSIGNED NOT NULL,
  PRIMARY KEY (bucket),
  KEY idx_rate_limits_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
