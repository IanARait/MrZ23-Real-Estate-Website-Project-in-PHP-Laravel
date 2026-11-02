USE realestate_db;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE property_features;
TRUNCATE TABLE property_images;
TRUNCATE TABLE saved_properties;
TRUNCATE TABLE inquiries;
TRUNCATE TABLE properties;
TRUNCATE TABLE agents;
TRUNCATE TABLE features;
TRUNCATE TABLE property_types;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO property_types (name, slug, icon, status) VALUES
('House', 'house', 'house', 1),
('Apartment', 'apartment', 'building', 1),
('Condo', 'condo', 'door-open', 1),
('Villa', 'villa', 'warehouse', 1),
('Land', 'land', 'tree', 1),
('Commercial', 'commercial', 'store', 1);

INSERT INTO features (name, icon, category, status) VALUES
('Swimming Pool', 'water', 'Outdoor', 1),
('Garage', 'car-front', 'Parking', 1),
('Central Air Conditioning', 'snow', 'Interior', 1),
('Fireplace', 'fire', 'Interior', 1),
('Garden', 'flower', 'Outdoor', 1),
('Balcony', 'lamp', 'Interior', 1),
('Security System', 'shield', 'Safety', 1),
('Gym', 'dumbbell', 'Amenities', 1),
('Elevator', 'arrows-move', 'Amenities', 1),
('Jacuzzi', 'bathtub', 'Outdoor', 1),
('Hardwood Floors', 'border-all', 'Interior', 1),
('Smart Home', 'phone', 'Technology', 1),
('High Speed Internet', 'wifi', 'Technology', 1),
('Waterfront', 'water', 'Location', 1),
('Mountain View', 'mountains', 'Location', 1);

INSERT INTO agents (full_name, email, phone, license_number, bio, facebook, twitter, linkedin, instagram, experience_years, specialties, status) VALUES
('Sarah Johnson', 'sarah@homehub.com', '+1 (555) 234-5678', 'LIC-1001', 'Senior real estate agent with a passion for helping families find their dream home.', 'https://facebook.com/sarah', 'https://twitter.com/sarah', 'https://linkedin.com/in/sarah', 'https://instagram.com/sarah', 8, 'Residential, Luxury Homes', 1),
('Michael Chen', 'michael@homehub.com', '+1 (555) 345-6789', 'LIC-1002', 'Luxury property specialist with a decade of experience in premium listings.', 'https://facebook.com/michael', 'https://twitter.com/michael', 'https://linkedin.com/in/michael', 'https://instagram.com/michael', 12, 'Luxury, Commercial', 1),
('Emily Rodriguez', 'emily@homehub.com', '+1 (555) 456-7890', 'LIC-1003', 'Commercial real estate expert focused on investment and industrial properties.', 'https://facebook.com/emily', 'https://twitter.com/emily', 'https://linkedin.com/in/emily', 'https://instagram.com/emily', 6, 'Commercial, Industrial', 1),
('David Thompson', 'david@homehub.com', '+1 (555) 567-8901', 'LIC-1004', 'Residential specialist committed to first-time buyers and growing families.', '', '', 'https://linkedin.com/in/david', '', 10, 'Residential, First-Time Buyers', 1),
('Jessica Williams', 'jessica@homehub.com', '+1 (555) 678-9012', 'LIC-1005', 'Property management expert with a focus on rentals.', '', '', '', 'https://instagram.com/jessica', 7, 'Property Management, Rentals', 1),
('Robert Martinez', 'robert@homehub.com', '+1 (555) 789-0123', 'LIC-1006', 'Investment property specialist for high-yield portfolios.', 'https://facebook.com/robert', 'https://twitter.com/robert', 'https://linkedin.com/in/robert', '', 15, 'Investment, Commercial', 1);

INSERT INTO properties (title, slug, description, price, property_type_id, status_id, bedrooms, bathrooms, sqft, lot_size, year_built, address_line1, address_line2, city, state, zip_code, country, featured_image, is_featured, status, views, agent_id, listing_date) VALUES
('Modern Family Home in Green Hills', 'modern-family-home-green-hills', 'A beautifully updated family home featuring an open floor plan, chef''s kitchen, and a large backyard perfect for entertaining. Walking distance to top-rated schools.', 450000.00, 1, 1, 4, 3, 2400.00, 0.25, 2018, '123 Main St', '', 'Green Hills', 'TN', '37060', 'US', 'p1.svg', 1, 1, 120, 1, '2026-08-15'),
('Downtown Luxury Condo', 'downtown-luxury-condo', 'Stunning corner unit with skyline views, floor-to-ceiling windows, and resort-style amenities including gym, pool and 24/7 concierge.', 320000.00, 3, 1, 2, 2, 1150.00, 0.00, 2021, '456 Oak Ave', 'Unit 1204', 'Nashville', 'TN', '37203', 'US', 'p2.svg', 1, 1, 88, 2, '2026-08-20'),
('Cozy Garden Apartment', 'cozy-garden-apartment', 'Charming garden-level apartment steps from local cafes and parks. Two bedrooms with a private patio and on-site parking.', 1650.00, 2, 2, 2, 1, 900.00, 0.00, 1998, '789 Maple Dr', '', 'Austin', 'TX', '78701', 'US', 'p3.svg', 0, 1, 45, 3, '2026-08-22'),
('Luxury Lakefront Villa', 'luxury-lakefront-villa', 'Exquisite lakefront villa with private dock, infinity pool, and panoramic water views from every room. An entertainer''s paradise.', 1250000.00, 4, 1, 5, 5, 5800.00, 1.20, 2020, '234 Shoreline Way', '', 'Lakewood', 'WA', '98498', 'US', 'p4.svg', 1, 1, 210, 2, '2026-08-10'),
('Commercial Retail Space', 'commercial-retail-space', 'Prime retail space in a high-traffic commercial district. 1,800 sqft with large storefront windows and ample parking.', 500000.00, 6, 1, 0, 1, 1800.00, 0.30, 2010, '567 Commerce Blvd', '', 'Phoenix', 'AZ', '85004', 'US', 'p5.svg', 0, 1, 32, 3, '2026-08-18'),
('Buildable Acreage', 'buildable-acreage', 'Beautiful 5-acre parcel zoned for residential development with mature trees and utilities at the street. Perfect for a custom build.', 175000.00, 5, 1, 0, 0, 0.00, 5.00, 0, '890 Field Rd', '', 'Boise', 'ID', '83702', 'US', 'p6.svg', 0, 1, 27, 5, '2026-08-25'),
('Sunny Suburban Rental', 'sunny-suburban-rental', 'Bright 3-bedroom rental in a quiet suburban neighborhood with a fenced backyard, finished basement, and attached garage.', 2100.00, 1, 2, 3, 2, 1600.00, 0.18, 2005, '345 Elm St', '', 'Denver', 'CO', '80014', 'US', 'p7.svg', 0, 1, 61, 4, '2026-08-27'),
('Upscale Penthouse Rental', 'upscale-penthouse-rental', 'Rare penthouse rental with wraparound terrace, gourmet kitchen, and skyline views. Includes underground parking and clubhouse access.', 3800.00, 3, 2, 3, 3, 2100.00, 0.00, 2019, '678 Skyline Ave', 'PH', 'Chicago', 'IL', '60654', 'US', 'p8.svg', 0, 1, 74, 6, '2026-08-28');

INSERT INTO property_images (property_id, image, sort_order) VALUES
(1, 'p1b.svg', 1), (1, 'p1.svg', 2),
(2, 'p2b.svg', 1), (2, 'p2.svg', 2),
(3, 'p3b.svg', 1), (3, 'p3.svg', 2),
(4, 'p4b.svg', 1), (4, 'p4.svg', 2);