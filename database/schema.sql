-- GlobeTrek Adventures Database Schema

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS travel_guides;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS queries;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS package_addons;
DROP TABLE IF EXISTS transport_options;
DROP TABLE IF EXISTS accommodations;
DROP TABLE IF EXISTS package_itineraries;
DROP TABLE IF EXISTS tour_packages;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS users;

-- 1. USERS
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('customer', 'staff', 'admin')),
    phone TEXT,
    address TEXT,
    avatar TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. DESTINATIONS
CREATE TABLE destinations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    region TEXT NOT NULL,
    description TEXT NOT NULL,
    highlights TEXT,
    image_url TEXT,
    best_season TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. TOUR PACKAGES
CREATE TABLE tour_packages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    destination_id INTEGER NOT NULL,
    duration_days INTEGER NOT NULL DEFAULT 3,
    duration_nights INTEGER NOT NULL DEFAULT 2,
    base_price REAL NOT NULL,
    difficulty TEXT CHECK(difficulty IN ('Easy', 'Moderate', 'Challenging', 'Demanding')) DEFAULT 'Moderate',
    category TEXT CHECK(category IN ('Adventure', 'Cultural', 'Nature', 'Coastal', 'Wildlife', 'Honeymoon')) DEFAULT 'Adventure',
    is_featured INTEGER DEFAULT 0,
    image_url TEXT,
    short_desc TEXT,
    full_desc TEXT,
    status TEXT CHECK(status IN ('Active', 'Draft', 'Archived')) DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- 4. PACKAGE ITINERARIES
CREATE TABLE package_itineraries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    package_id INTEGER NOT NULL,
    day_number INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    activity_icon TEXT DEFAULT 'compass',
    FOREIGN KEY (package_id) REFERENCES tour_packages(id) ON DELETE CASCADE
);

-- 5. ACCOMMODATION PARTNERS
CREATE TABLE accommodations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    destination_id INTEGER NOT NULL,
    tier TEXT CHECK(tier IN ('Standard', 'Deluxe', 'Luxury Resort')) NOT NULL DEFAULT 'Standard',
    price_per_night REAL NOT NULL DEFAULT 0.00,
    description TEXT,
    amenities TEXT,
    image_url TEXT,
    status TEXT DEFAULT 'Available',
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- 6. TRANSPORTATION FLEET
CREATE TABLE transport_options (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_name TEXT NOT NULL,
    vehicle_type TEXT CHECK(vehicle_type IN ('Private Sedan Car', 'AC Luxury Van', '4x4 Safari SUV', 'Mini Tourist Coach')) NOT NULL,
    capacity INTEGER NOT NULL DEFAULT 4,
    price_per_day REAL NOT NULL DEFAULT 0.00,
    features TEXT,
    image_url TEXT,
    status TEXT DEFAULT 'Available'
);

-- 7. PACKAGE ADD-ONS (Optional Extras)
CREATE TABLE package_addons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    description TEXT,
    icon TEXT DEFAULT 'plus-circle'
);

-- 8. BOOKINGS
CREATE TABLE bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_code TEXT UNIQUE NOT NULL,
    user_id INTEGER NOT NULL,
    package_id INTEGER NOT NULL,
    travel_date DATE NOT NULL,
    travelers_count INTEGER NOT NULL DEFAULT 1,
    accommodation_id INTEGER,
    transport_id INTEGER,
    add_ons_json TEXT,
    custom_notes TEXT,
    base_price REAL NOT NULL,
    hotel_price REAL DEFAULT 0,
    transport_price REAL DEFAULT 0,
    addons_price REAL DEFAULT 0,
    total_amount REAL NOT NULL,
    payment_status TEXT CHECK(payment_status IN ('Pending', 'Paid', 'Refunded')) DEFAULT 'Pending',
    booking_status TEXT CHECK(booking_status IN ('Pending', 'Confirmed', 'In-Progress', 'Completed', 'Cancelled')) DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES tour_packages(id) ON DELETE CASCADE,
    FOREIGN KEY (accommodation_id) REFERENCES accommodations(id) ON DELETE SET NULL,
    FOREIGN KEY (transport_id) REFERENCES transport_options(id) ON DELETE SET NULL
);

-- 9. PAYMENTS (Simulated Gateway Records)
CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    transaction_ref TEXT UNIQUE NOT NULL,
    payment_method TEXT CHECK(payment_method IN ('Credit Card', 'PayPal', 'Bank Transfer')) NOT NULL,
    amount REAL NOT NULL,
    payment_status TEXT CHECK(payment_status IN ('Completed', 'Pending', 'Failed', 'Refunded')) DEFAULT 'Completed',
    card_last4 TEXT,
    paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 10. CUSTOMER QUERIES / TICKETING
CREATE TABLE queries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    guest_name TEXT NOT NULL,
    guest_email TEXT NOT NULL,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    status TEXT CHECK(status IN ('Pending', 'In Review', 'Answered', 'Closed')) DEFAULT 'Pending',
    staff_reply TEXT,
    replied_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 11. REVIEWS
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    package_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    rating INTEGER CHECK(rating BETWEEN 1 AND 5) NOT NULL,
    comment TEXT NOT NULL,
    is_approved INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES tour_packages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 12. TRAVEL GUIDES & ARTICLES
CREATE TABLE travel_guides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    destination TEXT NOT NULL,
    category TEXT NOT NULL,
    read_time TEXT DEFAULT '5 min read',
    snippet TEXT NOT NULL,
    content TEXT NOT NULL,
    image_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 13. AUDIT LOGS (Security & Governance)
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- INDEXES FOR MAXIMUM QUERY PERFORMANCE
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_packages_dest ON tour_packages(destination_id);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_code ON bookings(booking_code);
CREATE INDEX idx_queries_status ON queries(status);
