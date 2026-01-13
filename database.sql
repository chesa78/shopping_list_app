CREATE DATABASE shopping_list_app;
USE shopping_list_app;

CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE shopping_lists (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(200) DEFAULT 'List Belanjaan',
    shopping_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    budget DECIMAL(10,2) DEFAULT 0,
    category VARCHAR(50) DEFAULT 'Umum',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE shopping_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    list_id INT(11) NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(50) DEFAULT '1',
    price DECIMAL(10,2) DEFAULT 0,
    is_checked BOOLEAN DEFAULT FALSE,
    priority ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    category VARCHAR(50) DEFAULT 'Lainnya',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE
);

CREATE TABLE shopping_history (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(200) NOT NULL,
    shopping_date DATE NOT NULL,
    completed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_items INT(11) DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0,
    budget DECIMAL(10,2) DEFAULT 0,
    category VARCHAR(50) DEFAULT 'Umum',
    notes TEXT,
    items_json TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE shopping_templates (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    template_name VARCHAR(200) NOT NULL,
    category VARCHAR(50) DEFAULT 'Umum',
    items_json TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);