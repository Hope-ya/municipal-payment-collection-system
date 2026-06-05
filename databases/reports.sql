CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    position ENUM('Treasurer', 'Collector') NOT NULL,
    report_type ENUM('Daily', 'Monthly', 'Quarterly', 'Yearly') NOT NULL,
    report_date DATE NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    submission_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_email VARCHAR(100) NOT NULL
);