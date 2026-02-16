-- Migration: create import_jobs table
-- Run manually: mysql -u user -p proservice < migrations/20260216_create_import_jobs.sql

CREATE TABLE IF NOT EXISTS `import_jobs` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empresa_id` INT NULL,
  `user_id` INT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'produtos',
  `original_filename` VARCHAR(255) NOT NULL,
  `stored_path` VARCHAR(255) NOT NULL,
  `status` ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `total_rows` INT DEFAULT 0,
  `processed_rows` INT DEFAULT 0,
  `progress` FLOAT DEFAULT 0,
  `result_json` LONGTEXT NULL,
  `error_text` LONGTEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` DATETIME NULL,
  `finished_at` DATETIME NULL,
  INDEX (`empresa_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
