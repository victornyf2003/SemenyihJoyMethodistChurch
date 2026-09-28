CREATE DATABASE IF NOT EXISTS joy_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE joy_db;

CREATE TABLE IF NOT EXISTS registration (
    registration_id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    age VARCHAR(10) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    member VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (registration_id),
    UNIQUE KEY uniq_email (email)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- Note: `password` stores a bcrypt hash produced by PHP's password_hash(),
-- never a plain-text password. `email` is unique so it can double as the
-- login username for both registration.php and login.php.
