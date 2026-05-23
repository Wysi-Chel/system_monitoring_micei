CREATE DATABASE IF NOT EXISTS system_monitoring_db;
USE system_monitoring_db;

CREATE TABLE IF NOT EXISTS `MICEI system monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_recorded DATE NOT NULL,
    transaction_date DATE NOT NULL,
    branch VARCHAR(100),
    department VARCHAR(100),
    module VARCHAR(100),
    user_name VARCHAR(150),
    invoice_reference VARCHAR(150),
    payment_reference VARCHAR(150),
    client_name VARCHAR(200),
    amount DECIMAL(15,2) NULL,
    reason TEXT,
    approved_by VARCHAR(150),
    processed_type VARCHAR(100),
    processed_by VARCHAR(150),
    remarks TEXT,
    classification VARCHAR(100),
    system_admin VARCHAR(150),
    ticket VARCHAR(150),
    status VARCHAR(100),
    offense VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `NTR system monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_recorded DATE NOT NULL,
    transaction_date DATE NOT NULL,
    branch VARCHAR(100),
    department VARCHAR(100),
    module VARCHAR(100),
    user_name VARCHAR(150),
    invoice_reference VARCHAR(150),
    payment_reference VARCHAR(150),
    client_name VARCHAR(200),
    amount DECIMAL(15,2) NULL,
    reason TEXT,
    approved_by VARCHAR(150),
    processed_type VARCHAR(100),
    processed_by VARCHAR(150),
    remarks TEXT,
    classification VARCHAR(100),
    system_admin VARCHAR(150),
    ticket VARCHAR(150),
    status VARCHAR(100),
    offense VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `MICEI ticket monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch VARCHAR(100),
    ticket_number VARCHAR(150) NOT NULL,
    ticket_description TEXT,
    date_created DATE NOT NULL,
    created_by VARCHAR(150),
    ticket_status VARCHAR(100) NOT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `NTR ticket monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch VARCHAR(100),
    ticket_number VARCHAR(150) NOT NULL,
    ticket_description TEXT,
    date_created DATE NOT NULL,
    created_by VARCHAR(150),
    ticket_status VARCHAR(100) NOT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `MICEI resolved ticket monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_ticket_id INT NOT NULL,
    branch VARCHAR(100),
    ticket_number VARCHAR(150) NOT NULL,
    ticket_description TEXT,
    date_created DATE NOT NULL,
    created_by VARCHAR(150),
    ticket_status VARCHAR(100) NOT NULL,
    resolved_at DATETIME NOT NULL,
    ticket_age_days INT NOT NULL DEFAULT 0,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_source_ticket_id (source_ticket_id)
);

CREATE TABLE IF NOT EXISTS `NTR resolved ticket monitoring` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_ticket_id INT NOT NULL,
    branch VARCHAR(100),
    ticket_number VARCHAR(150) NOT NULL,
    ticket_description TEXT,
    date_created DATE NOT NULL,
    created_by VARCHAR(150),
    ticket_status VARCHAR(100) NOT NULL,
    resolved_at DATETIME NOT NULL,
    ticket_age_days INT NOT NULL DEFAULT 0,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_source_ticket_id (source_ticket_id)
);
