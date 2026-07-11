-- ============================================
-- WanderLust Tours & Travels - Full Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS wanderlust_db;
USE wanderlust_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150),
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Destinations table
CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    country VARCHAR(100),
    description TEXT,
    image VARCHAR(300),
    category VARCHAR(100),
    mood_tags VARCHAR(300),
    best_months VARCHAR(200),
    avg_cost_per_day INT DEFAULT 0,
    latitude DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    carbon_per_km DECIMAL(5,3) DEFAULT 0.255,
    youtube_tour_id VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings / Trip plans
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    destination_id INT,
    trip_name VARCHAR(200),
    travel_date DATE,
    return_date DATE,
    num_travelers INT DEFAULT 1,
    budget DECIMAL(10,2),
    travel_mode VARCHAR(50),
    status ENUM('planned','confirmed','cancelled','completed') DEFAULT 'planned',
    itinerary TEXT,
    carbon_kg DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    destination_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- Contact enquiries
CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    email VARCHAR(150),
    phone VARCHAR(20),
    message TEXT,
    status ENUM('new','read','replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Sample Destinations
-- ============================================
INSERT INTO destinations (name, country, description, image, category, mood_tags, best_months, avg_cost_per_day, latitude, longitude, youtube_tour_id) VALUES
('Goa', 'India', 'Sun-kissed beaches, vibrant nightlife, Portuguese heritage, and fresh seafood make Goa India\'s top beach destination.', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=800&auto=format&fit=crop', 'Beach', 'Adventure,Relaxation,Romantic', 'November,December,January,February', 3000, 15.2993, 74.1240, 'wk6VofcAIl8'),
('Manali', 'India', 'Snow-capped peaks, adventure sports, lush valleys and Himalayan charm. Perfect for thrill-seekers and nature lovers.', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=800&auto=format&fit=crop', 'Mountain', 'Adventure,Family', 'March,April,May,June,October', 2500, 32.2432, 77.1892, 'YFbEIDonzHo'),
('Kerala Backwaters', 'India', 'Tranquil houseboat rides through lush green canals, spice gardens, and Ayurvedic retreats. Pure serenity.', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=800&auto=format&fit=crop', 'Nature', 'Relaxation,Romantic,Family', 'October,November,December,January,February', 4000, 9.4981, 76.3388, 'zGBBVGv4k54'),
('Rajasthan', 'India', 'Royal palaces, golden deserts, vibrant culture, and majestic forts. A journey through India\'s regal history.', 'https://images.unsplash.com/photo-1477587458883-47145ed6979e?w=800&auto=format&fit=crop', 'Cultural', 'Family,Romantic,Budget', 'October,November,December,January,February,March', 2800, 26.9124, 75.7873, 'RoI_8QAiaSw'),
('Andaman Islands', 'India', 'Crystal clear waters, pristine beaches, coral reefs, and spectacular marine life. India\'s tropical paradise.', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&auto=format&fit=crop', 'Beach', 'Adventure,Romantic,Relaxation', 'November,December,January,February,March,April', 5000, 11.6234, 92.7265, 'JVFxc-nAIrc'),
('Leh Ladakh', 'India', 'High altitude desert landscape, Buddhist monasteries, and some of the world\'s most scenic roads.', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format&fit=crop', 'Mountain', 'Adventure,Budget', 'June,July,August,September', 3500, 34.1526, 77.5771, 'oR0KSbT2BF8'),
('Coorg', 'India', 'Coffee plantations, misty hills, waterfalls, and wildlife. Karnataka\'s Scotland is pure bliss.', 'https://images.unsplash.com/photo-1605649461784-43cc2c57a55e?w=800&auto=format&fit=crop', 'Nature', 'Relaxation,Romantic,Family', 'October,November,December,January,February,March', 3200, 12.3375, 75.8069, 'YVE0Xy0VKQE'),
('Varanasi', 'India', 'The spiritual capital of India. Ancient ghats, sacred Ganges rituals, and timeless culture.', 'https://images.unsplash.com/photo-1561361058-c24e01238a46?w=800&auto=format&fit=crop', 'Spiritual', 'Spiritual,Family,Budget', 'October,November,December,January,February,March', 1800, 25.3176, 82.9739, 'VJRFkEsGvP4'),
('Rishikesh', 'India', 'The yoga capital of the world. Sacred Ganges ghats, ashrams, meditation retreats, and Himalayan serenity await the soul seeker.', 'https://images.unsplash.com/photo-1591018533559-b60e5eb5d9c4?w=800&auto=format&fit=crop', 'Spiritual', 'Spiritual,Adventure,Relaxation', 'February,March,April,September,October,November', 2000, 30.0869, 78.2676, 'lVHg3PcLJQ0'),
('Tirupati', 'India', 'Home to the famous Venkateswara Temple, one of the most visited pilgrimage sites in the world. A deeply sacred and serene experience.', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=800&auto=format&fit=crop', 'Spiritual', 'Spiritual,Family', 'September,October,November,December,January,February', 1500, 13.6288, 79.4192, 'rMVfJ3UJufs');

-- Default admin
INSERT INTO users (username, name, email, password, role) VALUES
('admin', 'Admin', 'admin@wanderlust.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;
