CREATE DATABASE IF NOT EXISTS system_monitoring_db;
USE system_monitoring_db;

CREATE TABLE IF NOT EXISTS monitoring_records (
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
