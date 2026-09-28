-- schema.sql
-- 3NF Normalized Database Schema for FLYME Vehicle Rental

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicle_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    page_url VARCHAR(100) NOT NULL,
    status ENUM('available', 'maintenance', 'rented') DEFAULT 'available',
    FOREIGN KEY (category_id) REFERENCES vehicle_categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    rental_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Seed Initial Data
INSERT INTO vehicle_categories (name) VALUES ('Car'), ('Bike'), ('Van'), ('Three-Wheel'), ('Double Cab');

INSERT INTO vehicles (category_id, name, image_url, price_per_day, description, page_url) VALUES 
(1, 'Premium Sedan', 'New folder/car.jpg', 15000.00, 'Travel in style and comfort with our premium sedans.', 'car.html'),
(2, 'Sport Bike', 'New folder/bike.jpg', 3500.00, 'Experience the thrill of the ride.', 'bike.html'),
(3, 'Passenger Van (KDH)', 'New folder/kdh.jpg', 25000.00, 'Perfect for group travels.', 'van.html'),
(4, 'Three-Wheel ABG2011', 'New folder/threeweel.jpg', 1500.00, 'Perfect for quick inner-city travel.', 'three-wheel.html'),
(5, '4x4 Double Cab', 'New folder/4.jpeg', 20000.00, 'Built for tough terrains.', 'double-cab.html'),
(3, 'VIP Executive Van', 'New folder/5.jpeg', 35000.00, 'Travel in ultimate comfort.', 'vip-van.html'),
(3, 'Adventure Camper Van', 'New folder/3.jpg', 45000.00, 'Embark on the ultimate road trip.', 'camper-van.html');
