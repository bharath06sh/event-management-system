-- Create Event Management System Database Tables

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS venues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    description TEXT,
    price_per_day DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    venue_id INT,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    total_tickets INT NOT NULL,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (venue_id) REFERENCES venues(id)
);

CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT,
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin table is deprecated - use users table with role='admin' instead

-- Insert sample admin user (password hashed with PHP password_hash: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@eventmanagement.com', '$2y$10$slYQmyNdGzin7olVN3p5OPST9/PgBkqquzi8Ay0IQlmpDtQw6p46a', 'admin');

-- Insert sample users
INSERT INTO users (username, email, password, role) VALUES 
('john_organizer', 'john@example.com', MD5('password123'), 'user'),
('jane_user', 'jane@example.com', MD5('password123'), 'user');

-- Insert sample venues
INSERT INTO venues (name, location, capacity, description, price_per_day, is_available) VALUES 
('Grand Ballroom', 'Downtown', 500, 'Elegant ballroom for events', 5000.00, TRUE),
('Conference Center', 'Business District', 300, 'Modern conference facility', 3000.00, TRUE),
('Outdoor Park', 'City Center', 1000, 'Beautiful outdoor venue', 2000.00, TRUE);
