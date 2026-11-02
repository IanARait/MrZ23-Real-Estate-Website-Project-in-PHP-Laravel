CREATE DATABASE IF NOT EXISTS realestate_db;
USE realestate_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'manager', 'agent') DEFAULT 'agent',
    status TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE property_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(20) DEFAULT '#6c757d',
    status TINYINT(1) DEFAULT 1
);

INSERT INTO property_statuses (name, slug, color) VALUES
('For Sale', 'for-sale', '#2563eb'),
('For Rent', 'for-rent', '#059669'),
('Sold', 'sold', '#dc2626'),
('Pending', 'pending', '#d97706'),
('Under Contract', 'under-contract', '#7c3aed');

CREATE TABLE features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    category VARCHAR(50),
    status TINYINT(1) DEFAULT 1
);

CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    property_type_id INT,
    status_id INT DEFAULT 1,
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    sqft DECIMAL(12,2) DEFAULT 0,
    lot_size DECIMAL(10,2) DEFAULT 0,
    year_built INT,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    zip_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'US',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    featured_image VARCHAR(255),
    gallery TEXT,
    virtual_tour_url VARCHAR(255),
    video_url VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    agent_id INT,
    listing_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES property_statuses(id) ON DELETE SET NULL
);

CREATE TABLE property_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    feature_id INT NOT NULL,
    value VARCHAR(255),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE
);

CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    photo VARCHAR(255),
    license_number VARCHAR(50),
    bio TEXT,
    facebook VARCHAR(255),
    twitter VARCHAR(255),
    linkedin VARCHAR(255),
    instagram VARCHAR(255),
    experience_years INT DEFAULT 0,
    specialties TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    customer_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    type ENUM('inquiry', 'showing', 'offer') DEFAULT 'inquiry',
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE TABLE saved_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (customer_id, property_id)
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'HomeHub - Real Estate'),
('site_email', 'info@homehub.com'),
('site_phone', '+1-555-123-4567'),
('currency_symbol', '$'),
('currency_code', 'USD'),
('commission_rate', '3.00'),
('items_per_page', '12'),
('maintenance_mode', '0'),
('theme_color', '#059669'),
('google_maps_key', ''),
('footer_text', '© 2024 HomeHub Real Estate');

INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@homehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
