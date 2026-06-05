CREATE TABLE download_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    download_time DATETIME NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;