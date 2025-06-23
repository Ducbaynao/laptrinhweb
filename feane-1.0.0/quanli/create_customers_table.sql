USE quanli;

CREATE TABLE IF NOT EXISTS customers (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    birthdate DATE,
    gender ENUM('Nam', 'Nữ', 'Khác') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
); 