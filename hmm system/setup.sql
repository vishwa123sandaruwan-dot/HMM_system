CREATE DATABASE IF NOT EXISTS teachflow_hmm;
USE teachflow_hmm;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-tag'
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    transaction_date DATE NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    month VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget (user_id, category_id, month)
);

-- Default Admin Account (username: admin, password: 123456)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@teachflow.lk', '$2y$10$LheB5P1HPuUl2aXkbbrRkOuX4MHcHW9UHTo3FRXcqeI0NwtrzOb.G', 'admin');

-- පෙරනිමි ශ්‍රී ලාංකික කාණ්ඩ
INSERT INTO categories (name, type, icon) VALUES 
('වැටුප', 'income', 'fa-wallet'),
('ව්‍යාපාරය', 'income', 'fa-briefcase'),
('ෆ්‍රීලාන්ස්', 'income', 'fa-laptop-code'),
('තෑගි', 'income', 'fa-gift'),
('වෙනත් ආදායම', 'income', 'fa-money-bill-wave'),
('සිල්ලර බඩු', 'expense', 'fa-shopping-basket'),
('විදුලිය (CEB)', 'expense', 'fa-bolt'),
('ජලය (NWSDB)', 'expense', 'fa-faucet'),
('අන්තර්ජාලය සහ මොබයිල්', 'expense', 'fa-wifi'),
('කුලිය / බදු', 'expense', 'fa-home'),
('ප්‍රවාහනය සහ ඉන්ධන', 'expense', 'fa-gas-pump'),
('අධ්‍යාපනය / ටියුශන්', 'expense', 'fa-graduation-cap'),
('සෞඛ්‍ය / වෛද්‍ය', 'expense', 'fa-heartbeat'),
('රක්ෂණය', 'expense', 'fa-shield-alt'),
('විනෝදාස්වාදය', 'expense', 'fa-film'),
('බයිට් / කෑම', 'expense', 'fa-utensils'),
('වෙනත් වියදම්', 'expense', 'fa-ellipsis-h');
