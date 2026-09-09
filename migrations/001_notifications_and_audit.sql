CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE SET NULL,
    INDEX idx_notifications_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS review_audit_logs (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    admin_id INT NOT NULL,
    action ENUM('approved', 'rejected') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_review_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notification_delivery_logs (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    channel ENUM('email') NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_error VARCHAR(500),
    next_attempt_at DATETIME,
    sent_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(notification_id) ON DELETE CASCADE,
    INDEX idx_delivery_retry (status, next_attempt_at)
) ENGINE=InnoDB;
