-- Multi-Brand Finance Management System Database Schema
-- Database: multibrand_fin

CREATE DATABASE IF NOT EXISTS `multibrand_fin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `multibrand_fin`;

-- 1. BRANDS TABLE
CREATE TABLE IF NOT EXISTS `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_name` VARCHAR(100) NOT NULL,
  `company_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'brand_user', 'manager') NOT NULL,
  `brand_id` INT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. USER BRANDS JOIN TABLE (Many-to-Many)
CREATE TABLE IF NOT EXISTS `user_brands` (
  `user_id` INT NOT NULL,
  `brand_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `brand_id`),
  CONSTRAINT `fk_ub_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ub_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BANK ACCOUNTS TABLE
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `account_holder_name` VARCHAR(100) NOT NULL,
  `account_number` VARCHAR(100) NOT NULL,
  `ifsc_code` VARCHAR(30) DEFAULT NULL,
  `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bank_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. FIXED MONTHLY EXPENSES TABLE
CREATE TABLE IF NOT EXISTS `fixed_expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `bank_account_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'Rent',
  `due_day` INT NOT NULL DEFAULT 1,
  `start_month` VARCHAR(7) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `note` TEXT DEFAULT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_fixed_exp_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fixed_exp_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fixed_exp_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. INTER-BRAND LOANS TABLE
CREATE TABLE IF NOT EXISTS `inter_brand_loans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `loan_number` VARCHAR(50) UNIQUE NOT NULL,
  `lender_brand_id` INT NOT NULL,
  `borrower_brand_id` INT NOT NULL,
  `lender_bank_account_id` INT NOT NULL,
  `borrower_bank_account_id` INT NOT NULL,
  `original_amount` DECIMAL(15,2) NOT NULL,
  `total_repaid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` DECIMAL(15,2) NOT NULL,
  `loan_date` DATE NOT NULL,
  `due_date` DATE DEFAULT NULL,
  `purpose` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'partially_repaid', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'active',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_loan_lender` FOREIGN KEY (`lender_brand_id`) REFERENCES `brands`(`id`),
  CONSTRAINT `fk_loan_borrower` FOREIGN KEY (`borrower_brand_id`) REFERENCES `brands`(`id`),
  CONSTRAINT `fk_loan_lender_bank` FOREIGN KEY (`lender_bank_account_id`) REFERENCES `bank_accounts`(`id`),
  CONSTRAINT `fk_loan_borrower_bank` FOREIGN KEY (`borrower_bank_account_id`) REFERENCES `bank_accounts`(`id`),
  CONSTRAINT `fk_loan_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. LOAN REPAYMENTS TABLE
CREATE TABLE IF NOT EXISTS `loan_repayments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `loan_id` INT NOT NULL,
  `repayment_number` VARCHAR(50) UNIQUE NOT NULL,
  `from_brand_id` INT NOT NULL,
  `to_brand_id` INT NOT NULL,
  `from_bank_account_id` INT NOT NULL,
  `to_bank_account_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_reference` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('completed', 'reversed') NOT NULL DEFAULT 'completed',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_repay_loan` FOREIGN KEY (`loan_id`) REFERENCES `inter_brand_loans`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_repay_from_brand` FOREIGN KEY (`from_brand_id`) REFERENCES `brands`(`id`),
  CONSTRAINT `fk_repay_to_brand` FOREIGN KEY (`to_brand_id`) REFERENCES `brands`(`id`),
  CONSTRAINT `fk_repay_from_bank` FOREIGN KEY (`from_bank_account_id`) REFERENCES `bank_accounts`(`id`),
  CONSTRAINT `fk_repay_to_bank` FOREIGN KEY (`to_bank_account_id`) REFERENCES `bank_accounts`(`id`),
  CONSTRAINT `fk_repay_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TRANSACTIONS TABLE
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `bank_account_id` INT NOT NULL,
  `type` ENUM('income', 'expense', 'loan_received', 'loan_given', 'loan_repayment', 'loan_repayment_received') NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `purpose` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `related_brand_id` INT DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT DEFAULT NULL,
  `transaction_date` DATE NOT NULL,
  `note` TEXT DEFAULT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_txn_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_txn_bank` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_txn_related_brand` FOREIGN KEY (`related_brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SYSTEM SETTINGS TABLE
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('fixed_expense_notification_days', '7');

