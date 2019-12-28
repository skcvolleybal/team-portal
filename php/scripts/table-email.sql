CREATE TABLE teamportal_email (
  id int AUTO_INCREMENT PRIMARY KEY,
  sender_email VARCHAR(64) NOT NULL,
  sender_naam VARCHAR(64) NOT NULL,
  receiver_email VARCHAR(64) NOT NULL,
  receiver_naam VARCHAR(64) NOT NULL,
  titel VARCHAR(128) NOT NULL,
  body TEXT NOT NULL,
  signature varchar(40) NOT NULL,
  queue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  send_date TIMESTAMP NULL,
  UNIQUE KEY(signature)
)