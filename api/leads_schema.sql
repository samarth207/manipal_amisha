-- Run this once in Hostinger phpMyAdmin (SQL tab) to create the shared leads table.
CREATE TABLE IF NOT EXISTS leads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  course VARCHAR(100) NOT NULL,
  consent TINYINT(1) NOT NULL DEFAULT 0,
  source VARCHAR(100) NOT NULL DEFAULT 'website',
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_email (email),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
