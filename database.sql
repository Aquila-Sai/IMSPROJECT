-- database.sql - Lumen Database Schema
-- Run this to create the complete database structure

CREATE DATABASE IF NOT EXISTS lumen_db;
USE lumen_db;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    cpu VARCHAR(100),
    ram VARCHAR(50),
    storage VARCHAR(50),
    screen_size VARCHAR(20),
    stock INT DEFAULT 0,
    image_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'Processing',
    tracking_number VARCHAR(100),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items Table
CREATE TABLE order_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Reviews Table
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Admin Logs Table
CREATE TABLE admin_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action TEXT NOT NULL,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id)
);

-- Insert Sample Admin User
-- Password: admin123
INSERT INTO users (name, email, password, phone, address, is_admin) VALUES
('Admin User', 'admin@lumen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'Admin Address', 1);

-- Insert Sample Products
INSERT INTO products (name, brand, price, description, cpu, ram, storage, screen_size, stock, image_url) VALUES
('ThinkPad X1 Carbon Gen 11', 'Lenovo', 85000.00, 'Premium business ultrabook with exceptional battery life and lightweight design.', 'Intel Core i7-1365U', '16GB LPDDR5', '512GB SSD', '14"', 15, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500'),
('MacBook Pro 14"', 'Apple', 125000.00, 'Powerful laptop with M3 Pro chip for creative professionals.', 'Apple M3 Pro', '18GB Unified', '512GB SSD', '14.2"', 10, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500'),
('ROG Strix G16', 'ASUS', 95000.00, 'High-performance gaming laptop with RGB lighting and powerful specs.', 'Intel Core i7-13650HX', '16GB DDR5', '1TB SSD', '16"', 8, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500'),
('Pavilion 15', 'HP', 45000.00, 'Affordable everyday laptop perfect for students and professionals.', 'Intel Core i5-1235U', '8GB DDR4', '512GB SSD', '15.6"', 20, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500'),
('Inspiron 14', 'Dell', 52000.00, 'Reliable business laptop with long battery life.', 'Intel Core i5-1335U', '8GB DDR4', '256GB SSD', '14"', 12, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=500'),
('Surface Laptop 5', 'Microsoft', 78000.00, 'Elegant touchscreen laptop with premium build quality.', 'Intel Core i7-1255U', '16GB LPDDR5', '512GB SSD', '13.5"', 7, 'https://images.unsplash.com/photo-1484788984921-03950022c9ef?w=500'),
('Predator Helios 300', 'Acer', 88000.00, 'Gaming powerhouse with excellent cooling and performance.', 'Intel Core i7-13700HX', '16GB DDR5', '1TB SSD', '15.6"', 5, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500'),
('Gram 17', 'LG', 72000.00, 'Ultra-lightweight 17-inch laptop with exceptional portability.', 'Intel Core i7-1360P', '16GB DDR5', '512GB SSD', '17"', 6, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500'),
('Legion 5 Pro', 'Lenovo', 92000.00, 'Professional gaming laptop with balanced performance.', 'AMD Ryzen 7 7745HX', '16GB DDR5', '1TB SSD', '16"', 9, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500'),
('ZenBook 14', 'ASUS', 68000.00, 'Slim and stylish ultrabook for on-the-go professionals.', 'Intel Core i7-1355U', '16GB LPDDR5', '512GB SSD', '14"', 11, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500');

-- Insert Sample Customer
-- Password: customer123
INSERT INTO users (name, email, password, phone, address, is_admin) VALUES
('John Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09987654321', '123 Sample St, Manila, Philippines', 0);