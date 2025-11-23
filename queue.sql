CREATE TABLE queue (
  id INT AUTO_INCREMENT PRIMARY KEY,
  queue_number INT,
  student_id VARCHAR(50),
  full_name VARCHAR(100),
  year_section VARCHAR(100),
  purpose TEXT,
  status ENUM('Waiting', 'Serving', 'Done') DEFAULT 'Waiting',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE qr_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  qr_token VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
