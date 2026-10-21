-- Persomy : système de blog (articles, catégories, traductions FR/EN/AR). MySQL 5.7+/8 ou MariaDB 10.4+, utf8mb4.

CREATE TABLE IF NOT EXISTS blog_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_categories_slug (slug),
  KEY idx_blog_categories_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_category_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  name VARCHAR(150) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_category_locale (category_id, locale),
  CONSTRAINT fk_blog_cat_tr_category FOREIGN KEY (category_id) REFERENCES blog_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  category_id INT UNSIGNED NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  cover_image VARCHAR(80) NULL,
  author VARCHAR(120) NOT NULL DEFAULT '',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_posts_slug (slug),
  KEY idx_blog_posts_listing (status, published_at),
  KEY idx_blog_posts_category (category_id),
  CONSTRAINT fk_blog_posts_category FOREIGN KEY (category_id) REFERENCES blog_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id INT UNSIGNED NOT NULL,
  locale VARCHAR(5) NOT NULL,
  title VARCHAR(200) NOT NULL DEFAULT '',
  excerpt VARCHAR(500) NOT NULL DEFAULT '',
  content MEDIUMTEXT NULL,
  meta_title VARCHAR(200) NOT NULL DEFAULT '',
  meta_description VARCHAR(320) NOT NULL DEFAULT '',
  image_alt VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_post_locale (post_id, locale),
  CONSTRAINT fk_blog_post_tr_post FOREIGN KEY (post_id) REFERENCES blog_posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
