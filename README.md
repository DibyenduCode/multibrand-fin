# 💼 Multi-Brand Corporate Treasury & Financial Operations System

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Font Awesome](https://img.shields.io/badge/Font_Awesome-6.5-528DD7?style=for-the-badge&logo=fontawesome&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)

<p align="center">
  <b>A modern, enterprise-grade multi-tenant financial operations and corporate treasury management platform built for business conglomerates, holding companies, and sister brands.</b>
</p>

</div>

---

## 🌟 Overview

The **Multi-Brand Corporate Treasury & Financial Operations System** provides complete real-time visibility and auditability over liquid cash reserves, bank accounts, income streams, operational expenses, recurring commitments, inter-brand liquidity transfers, and liabilities across multiple sister companies.

Designed with **strict data isolation**, granular **Role-Based Access Control (RBAC)**, and **automated email notification engine**, this platform empowers executives and brand managers to maintain full financial control.

---

## ✨ Key Features

### 🏢 Multi-Brand & Group Treasury
- **Consolidated Dashboard**: View entire business group liquidity, combined bank reserves, monthly cash movements, and group totals.
- **Brand Isolation**: Brand-specific dashboard tailored automatically based on user brand assignments.
- **Corporate Directory**: Unified directory for company legal entities, tax details, contact information, and registration addresses.

### 💳 Real-Time Bank Accounts & Cash Reserves
- Track opening balances, deposits, withdrawals, and real-time available liquid balances across unlimited corporate bank accounts.
- Multi-account support per brand with active/inactive account status management.

### 📝 Transaction History & Filtered CSV Export
- Comprehensive financial audit trail logging all money movements: Income, Expenses, Inter-Brand Loans, and Repayments.
- **Filter-Aware CSV Exporter**: Stream complete transaction history or filtered subsets (by Brand, Type, Date Period, or Custom Range) directly to Excel-ready CSV files (`.csv`).

### ⏰ Fixed Monthly Expenses & Automated Email Reminders
- Manage recurring monthly operational expenses (Office Rent, Staff Salaries, Servers, SaaS Subscriptions, Utilities).
- **Automated Lead-Time Bell Notifications**: Highlights pending fixed expenses due within custom lead days (e.g. 5 days prior to due day).
- **PHPMailer Integration**: Send instant email reminders to brand administrators or run via background CLI cron scripts.

### 🤝 Inter-Brand Liquidity Loans & Repayments
- Internal liquidity support mechanism between sister brands without third-party bank fees.
- Real-time tracking of **Lender Brands** vs. **Borrower Brands**, principal balances, total repayments, and remaining outstanding amounts.
- **Liabilities & Receivables Balance Sheets**: Dedicated real-time summary cards for "Money Needed to Pay" vs "Money to be Received".

### 📊 Advanced Financial Reports
- **Daily Cash Report**: Real-time daily inflow/outflow breakdown.
- **Monthly Summary Report**: Month-over-month cash movement analysis.
- **Brand Financial Matrix**: Individual brand comparison matrix displaying Available Cash, Total Income, Expenses, Loans Taken, Loans Given, Outstanding Liabilities, and Receivables.
- **Inter-Brand Loan Position Report**: Complete group lending position audit.

### 🔐 Role-Based Access Control (RBAC)
- 👑 **Super Admin**: Full access to all brands, global treasury, bank accounts, SMTP configuration, and user management.
- 🏢 **Brand User**: Access restricted strictly to assigned brand(s). Can be assigned to single or multiple brands.
- 👁️ **Manager (Read-Only)**: View-only access across assigned brand data without modification rights.

---

## 🛠️ Technology Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend Core** | PHP 8.1+ | Native OOP PHP MVC architecture |
| **Database** | MySQL 8.0+ / MariaDB | Relational InnoDB database with foreign key constraints |
| **Styling & UI** | Tailwind CSS 3.4 | Modern, responsive utility-first CSS UI layout |
| **Icons** | FontAwesome 6 | Clean vector iconography |
| **Mailer Engine** | PHPMailer 6.9 | SMTP email delivery for notifications & alerts |
| **Popups & Alerts** | SweetAlert2 | Interactive modal confirmations and flash toast notifications |

---

## 📁 Directory Structure

```text
fin/
├── app/
│   ├── Controllers/         # Application controllers (Auth, Transaction, Report, etc.)
│   ├── Helpers/             # Utilities (Auth, Format, Security, Pagination, CsvExporter, Mailer)
│   └── Models/              # Data models (Brand, BankAccount, Transaction, Loan, FixedExpense)
├── bin/
│   └── send_fixed_expense_notifications.php  # CLI cron script for automated email alerts
├── config/
│   ├── config.php           # Global configuration & base URL
│   ├── database.php         # PDO Database connection pool
│   └── smtp_config.php      # SMTP configuration file
├── database/
│   └── schema.sql           # Database schema & initial setup SQL
├── public/
│   ├── index.php            # Main router entry point
│   ├── assets/              # Custom stylesheets & images
│   └── uploads/             # Brand logos & attachments
├── views/
│   ├── layouts/             # Shared UI header, sidebar, topbar, alerts, footer
│   ├── transactions/        # Transaction ledger views & CSV export
│   ├── fixed_expenses/      # Recurring expenses views
│   ├── bank_accounts/       # Treasury & bank account management views
│   ├── loans/               # Inter-brand loans & repayment views
│   ├── reports/             # Daily, monthly, brand & loans report views
│   ├── users/               # User administration views
│   └── settings/            # SMTP & system settings views
├── index.php                # Root forwarding controller
└── README.md                # Documentation
```

---

## 🚀 Quick Setup & Installation

### 1. Prerequisites
- **Web Server**: Apache / Nginx (XAMPP, WAMP, LARAGON, or Linux stack)
- **PHP**: Version 8.1 or higher (with `pdo_mysql`, `curl`, and `openssl` extensions enabled)
- **Database**: MySQL 5.7+ / 8.0+ or MariaDB 10.4+

### 2. Clone Repository
```bash
git clone https://github.com/DibyenduCode/multibrand-fin.git
cd multibrand-fin
```

### 3. Database Setup
1. Create a MySQL database named `multibrand_fin` (or your preferred database name):
   ```sql
   CREATE DATABASE `multibrand_fin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the schema file located at `database/schema.sql`:
   ```bash
   mysql -u root -p multibrand_fin < database/schema.sql
   ```

### 4. Configuration
Open `config/config.php` and `config/database.php` to set your local environment settings:

```php
// config/config.php
define('BASE_URL', 'http://localhost/fin'); // Adjust according to your local path
```

```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'multibrand_fin');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Default Credentials

The schema creates a default Super Admin account upon import:

| Role | Email | Default Password |
| :--- | :--- | :--- |
| 👑 **Super Admin** | `admin@admin.com` | `admin123` |

> ⚠️ **Security Warning**: Please immediately change the default admin password in **Profile Settings** after logging in!

---

## ⏰ Automated Cron Setup (Email Reminders)

To automate daily email reminders for fixed monthly expenses due soon, configure a system **Cron Job** to run the CLI notification script daily:

```bash
# Run every day at 09:00 AM
0 9 * * * /usr/bin/php /var/www/html/fin/bin/send_fixed_expense_notifications.php >> /var/log/fixed_expenses_cron.log 2>&1
```

---

## 📊 CSV Export Usage

You can export transaction records to a CSV file directly from the **Transaction History & Audit Trail** view (`/transactions`):
1. Navigate to **Transaction History & Audit Trail**.
2. Optionally apply filters (by **Brand**, **Type**, or **Date Period**).
3. Click the **Export CSV** button in the top action bar.
4. The system will stream a UTF-8 BOM encoded `.csv` file formatted for Excel or Google Sheets containing all filtered/unfiltered records!

---

## 📄 License

Distributed under the **MIT License**. See `LICENSE` for more information.

---

<div align="center">
  <sub>Built with ❤️ for Multi-Brand Corporate Excellence</sub>
</div>
