-- =====================================================
-- GeoBoard Database Rework (Core + Compatible)
-- =====================================================

DROP DATABASE IF EXISTS geoboard_db;
CREATE DATABASE geoboard_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE geoboard_db;

-- 1) USERS AND AUTH
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    suffix VARCHAR(20) NULL,
    contact_number VARCHAR(20) NULL,
    alternate_contact VARCHAR(20) NULL,
    profile_photo VARCHAR(255) DEFAULT 'default-profile.jpg',
    role ENUM('admin', 'owner', 'manager', 'tenant') NOT NULL,
    email_verified_at TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended', 'pending') DEFAULT 'pending',
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    FULLTEXT INDEX ft_names (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) PROFILE EXTENSIONS
CREATE TABLE owner_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    company_name VARCHAR(255) NULL,
    business_permit_number VARCHAR(100) NULL,
    valid_id_type ENUM('driver_license', 'passport', 'national_id', 'tin', 'other') NOT NULL,
    valid_id_number VARCHAR(100) NOT NULL,
    valid_id_file VARCHAR(255) NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tenant_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    student_id VARCHAR(50) NULL,
    school_company VARCHAR(255) NOT NULL,
    course_or_position VARCHAR(100) NULL,
    valid_id_type ENUM('school_id', 'driver_license', 'passport', 'national_id', 'other') NOT NULL,
    valid_id_number VARCHAR(100) NOT NULL,
    valid_id_file VARCHAR(255) NOT NULL,
    emergency_contact_name VARCHAR(255) NOT NULL,
    emergency_contact_number VARCHAR(20) NOT NULL,
    id_verified BOOLEAN DEFAULT FALSE,
    verified_by BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    preferred_language ENUM('english', 'tagalog', 'cebuano', 'ilocano', 'other') DEFAULT 'english',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    employee_id VARCHAR(50) UNIQUE NULL,
    department VARCHAR(100) NULL,
    position VARCHAR(100) NULL,
    access_level ENUM('super_admin', 'moderator', 'support') DEFAULT 'moderator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) GEO TABLES
CREATE TABLE regions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    psgc_code VARCHAR(20) UNIQUE NULL,
    region_code VARCHAR(10) UNIQUE NOT NULL,
    region_name VARCHAR(255) NOT NULL,
    region_short_name VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE provinces (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    psgc_code VARCHAR(20) UNIQUE NULL,
    province_code VARCHAR(10) UNIQUE NOT NULL,
    province_name VARCHAR(255) NOT NULL,
    region_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cities_municipalities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    psgc_code VARCHAR(20) UNIQUE NULL,
    city_code VARCHAR(10) UNIQUE NOT NULL,
    city_name VARCHAR(255) NOT NULL,
    province_id BIGINT UNSIGNED NOT NULL,
    city_type ENUM('city', 'municipality') DEFAULT 'municipality',
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE barangays (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    psgc_code VARCHAR(20) UNIQUE NULL,
    barangay_code VARCHAR(10) UNIQUE NOT NULL,
    barangay_name VARCHAR(255) NOT NULL,
    city_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    FOREIGN KEY (city_id) REFERENCES cities_municipalities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) BOARDING HOUSES + ROOMS
CREATE TABLE boarding_houses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    owner_profile_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    slug VARCHAR(255) UNIQUE NULL,
    house_number VARCHAR(50) NULL,
    street_name VARCHAR(255) NULL,
    subdivision VARCHAR(255) NULL,
    barangay_id BIGINT UNSIGNED NULL,
    city_id BIGINT UNSIGNED NULL,
    province_id BIGINT UNSIGNED NULL,
    region_id BIGINT UNSIGNED NULL,
    zip_code VARCHAR(10) NULL,
    full_address TEXT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    nearby_landmarks TEXT NULL,
    property_type ENUM('dormitory', 'apartment', 'boarding_house', 'bedspace', 'other') DEFAULT 'boarding_house',
    total_floors INT DEFAULT 1,
    total_rooms INT DEFAULT 0,
    total_beds INT DEFAULT 0,
    total_tenants_current INT DEFAULT 0,
    max_capacity INT DEFAULT 0,
    featured_image VARCHAR(255) DEFAULT 'default-house.jpg',
    photos JSON NULL,
    house_rules TEXT NULL,
    curfew_time TIME NULL,
    allows_visitors BOOLEAN DEFAULT TRUE,
    allows_pets BOOLEAN DEFAULT FALSE,
    smoking_policy ENUM('allowed', 'not_allowed', 'designated_area') DEFAULT 'not_allowed',
    has_cctv BOOLEAN DEFAULT FALSE,
    has_security_guard BOOLEAN DEFAULT FALSE,
    security_deposit_required BOOLEAN DEFAULT TRUE,
    security_deposit_amount DECIMAL(10, 2) NULL,
    utilities_included JSON NULL,
    min_stay_months INT DEFAULT 1,
    max_stay_months INT NULL,
    payment_due_day INT DEFAULT 5,
    contact_person VARCHAR(255) NULL,
    contact_number VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    status ENUM('draft', 'pending', 'approved', 'rejected', 'suspended', 'closed') DEFAULT 'pending',
    approval_date DATE NULL,
    approved_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    featured_until DATE NULL,
    views_count INT DEFAULT 0,
    inquiry_count INT DEFAULT 0,
    favorite_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (owner_profile_id) REFERENCES owner_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (barangay_id) REFERENCES barangays(id) ON DELETE SET NULL,
    FOREIGN KEY (city_id) REFERENCES cities_municipalities(id) ON DELETE SET NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE SET NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_location (latitude, longitude),
    FULLTEXT INDEX ft_search (name, description, full_address, nearby_landmarks)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE room_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    capacity INT DEFAULT 1,
    monthly_rate DECIMAL(10, 2) NOT NULL,
    weekly_rate DECIMAL(10, 2) NULL,
    daily_rate DECIMAL(10, 2) NULL,
    total_rooms INT NOT NULL DEFAULT 0,
    available_rooms INT NOT NULL DEFAULT 0,
    occupied_rooms INT DEFAULT 0,
    reserved_rooms INT DEFAULT 0,
    maintenance_rooms INT DEFAULT 0,
    amenities JSON NULL,
    furniture_provided JSON NULL,
    is_available BOOLEAN DEFAULT TRUE,
    gender_restriction ENUM('any', 'male_only', 'female_only') DEFAULT 'any',
    student_only BOOLEAN DEFAULT FALSE,
    working_only BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    INDEX idx_price (monthly_rate),
    INDEX idx_boarding_house (boarding_house_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rooms (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    room_category_id BIGINT UNSIGNED NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    room_name VARCHAR(100) NULL,
    floor INT NULL,
    status ENUM('available', 'occupied', 'reserved', 'maintenance', 'cleaning') DEFAULT 'available',
    current_tenant_id BIGINT UNSIGNED NULL,
    condition_rating INT NULL CHECK (condition_rating BETWEEN 1 AND 5),
    has_own_cr BOOLEAN DEFAULT FALSE,
    has_own_kitchen BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_category_id) REFERENCES room_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_number (room_category_id, room_number),
    INDEX idx_status (status),
    INDEX idx_current_tenant (current_tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tenancy_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_profile_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    monthly_rate DECIMAL(10, 2) NOT NULL,
    security_deposit DECIMAL(10, 2) NULL,
    advance_payment DECIMAL(10, 2) NULL,
    status ENUM('pending', 'active', 'completed', 'terminated', 'evicted') DEFAULT 'pending',
    last_payment_date DATE NULL,
    next_payment_due DATE NULL,
    outstanding_balance DECIMAL(10, 2) DEFAULT 0,
    payment_status ENUM('current', 'late', 'overdue') DEFAULT 'current',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_active (status, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE rooms
ADD CONSTRAINT fk_rooms_current_tenant
FOREIGN KEY (current_tenant_id) REFERENCES tenancy_records(id) ON DELETE SET NULL;

CREATE TABLE payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenancy_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    due_date DATE NULL,
    payment_type ENUM('rent', 'deposit', 'advance', 'electricity', 'water', 'penalty', 'other') DEFAULT 'rent',
    payment_method ENUM('cash', 'bank_transfer', 'gcash', 'maya', 'paymaya', 'credit_card', 'debit_card', 'check', 'other') DEFAULT 'cash',
    reference_number VARCHAR(100) NULL,
    status ENUM('pending', 'confirmed', 'failed', 'refunded') DEFAULT 'pending',
    confirmed_by BIGINT UNSIGNED NULL,
    confirmed_at TIMESTAMP NULL,
    notes TEXT NULL,
    is_late BOOLEAN DEFAULT FALSE,
    penalty_amount DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenancy_id) REFERENCES tenancy_records(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) TENANT INTERACTION MODULES
CREATE TABLE favorites (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_profile_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (tenant_profile_id, boarding_house_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inquiries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    inquiry_number VARCHAR(50) UNIQUE NULL,
    tenant_profile_id BIGINT UNSIGNED NOT NULL,
    owner_profile_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    room_category_id BIGINT UNSIGNED NULL,
    message TEXT NOT NULL,
    preferred_move_in_date DATE NULL,
    preferred_stay_duration INT NULL,
    number_of_occupants INT DEFAULT 1,
    status ENUM('pending', 'read', 'replied', 'reserved', 'cancelled', 'closed') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    response_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_profile_id) REFERENCES owner_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    FOREIGN KEY (room_category_id) REFERENCES room_categories(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inquiry_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    inquiry_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    sender_role ENUM('tenant', 'owner', 'admin') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_profile_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    tenancy_id BIGINT UNSIGNED NULL,
    overall_rating INT NOT NULL CHECK (overall_rating BETWEEN 1 AND 5),
    location_rating INT NULL CHECK (location_rating BETWEEN 1 AND 5),
    value_for_money_rating INT NULL CHECK (value_for_money_rating BETWEEN 1 AND 5),
    cleanliness_rating INT NULL CHECK (cleanliness_rating BETWEEN 1 AND 5),
    security_rating INT NULL CHECK (security_rating BETWEEN 1 AND 5),
    landlord_rating INT NULL CHECK (landlord_rating BETWEEN 1 AND 5),
    amenities_rating INT NULL CHECK (amenities_rating BETWEEN 1 AND 5),
    title VARCHAR(255) NULL,
    comment TEXT NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenancy_id) REFERENCES tenancy_records(id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (tenant_profile_id, boarding_house_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comparison_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_profile_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(100) NULL,
    name VARCHAR(100) DEFAULT 'My Comparison',
    boarding_house_ids JSON NOT NULL,
    notes TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    share_token VARCHAR(100) UNIQUE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenance_requests (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    request_number VARCHAR(50) UNIQUE NULL,
    tenant_profile_id BIGINT UNSIGNED NOT NULL,
    tenancy_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    issue_type ENUM('plumbing', 'electrical', 'appliance', 'furniture', 'structural', 'pest', 'cleaning', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent', 'emergency') DEFAULT 'medium',
    assigned_to BIGINT UNSIGNED NULL,
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_profile_id) REFERENCES tenant_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (tenancy_id) REFERENCES tenancy_records(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE complaints (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    complaint_number VARCHAR(50) UNIQUE NULL,
    complainant_id BIGINT UNSIGNED NOT NULL,
    complainant_role ENUM('tenant', 'owner', 'visitor', 'neighbor') NOT NULL,
    respondent_id BIGINT UNSIGNED NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    tenancy_id BIGINT UNSIGNED NULL,
    complaint_type ENUM('noise', 'safety', 'harassment', 'cleanliness', 'security', 'policy_violation', 'damage', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'investigating', 'resolved', 'dismissed', 'escalated') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complainant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (respondent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenancy_id) REFERENCES tenancy_records(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'file') DEFAULT 'text',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) SAMPLE DATA
INSERT INTO regions (psgc_code, region_code, region_name, region_short_name) VALUES
('110000000', '11', 'Davao Region', 'Region XI'),
('160000000', '16', 'National Capital Region', 'NCR');

INSERT INTO provinces (psgc_code, province_code, province_name, region_id) VALUES
('112400000', '1124', 'Davao del Sur', (SELECT id FROM regions WHERE region_code = '11'));

INSERT INTO cities_municipalities (psgc_code, city_code, city_name, province_id, city_type) VALUES
('112405000', '112405', 'Digos City', (SELECT id FROM provinces WHERE province_code = '1124'), 'city');

INSERT INTO barangays (psgc_code, barangay_code, barangay_name, city_id, latitude, longitude) VALUES
('112405001', '112405001', 'Aplaya', (SELECT id FROM cities_municipalities WHERE city_code = '112405'), 6.74900000, 125.35700000),
('112405002', '112405002', 'Balabag', (SELECT id FROM cities_municipalities WHERE city_code = '112405'), 6.75200000, 125.35100000);

-- password: admin123
INSERT INTO users (email, password_hash, first_name, last_name, role, status, email_verified_at)
VALUES ('admin@geoboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', NOW());

INSERT INTO admin_profiles (user_id, employee_id, department, position, access_level)
VALUES (1, 'ADM001', 'Information Technology', 'System Administrator', 'super_admin');

INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'GeoBoard', 'text', 'Website name', true),
('site_description', 'Boarding House Management and Comparison System with Geotagging', 'text', 'Website description', true),
('contact_email', 'support@geoboard.com', 'text', 'Support email address', true),
('max_comparison_items', '5', 'number', 'Maximum items to compare at once', true);

-- 7) VIEWS
CREATE VIEW vw_boarding_house_summary AS
SELECT
    bh.id,
    bh.name,
    bh.slug,
    CONCAT_WS(', ', bh.house_number, bh.street_name, b.barangay_name, cm.city_name, p.province_name) AS full_address,
    bh.latitude,
    bh.longitude,
    bh.featured_image,
    bh.status,
    bh.is_featured,
    bh.views_count,
    bh.favorite_count,
    MIN(rc.monthly_rate) AS min_price,
    MAX(rc.monthly_rate) AS max_price,
    SUM(rc.total_rooms) AS total_rooms,
    SUM(rc.available_rooms) AS total_available_rooms,
    COUNT(DISTINCT rc.id) AS room_types_count,
    ROUND(AVG(r.overall_rating), 1) AS avg_rating,
    COUNT(DISTINCT r.id) AS review_count
FROM boarding_houses bh
LEFT JOIN barangays b ON bh.barangay_id = b.id
LEFT JOIN cities_municipalities cm ON bh.city_id = cm.id
LEFT JOIN provinces p ON bh.province_id = p.id
LEFT JOIN room_categories rc ON bh.id = rc.boarding_house_id
LEFT JOIN reviews r ON bh.id = r.boarding_house_id AND r.status = 'approved'
GROUP BY bh.id;

CREATE VIEW vw_owner_dashboard AS
SELECT
    op.user_id,
    op.id AS owner_profile_id,
    COUNT(DISTINCT bh.id) AS total_properties,
    SUM(CASE WHEN bh.status = 'approved' THEN 1 ELSE 0 END) AS approved_properties,
    SUM(CASE WHEN bh.status = 'pending' THEN 1 ELSE 0 END) AS pending_properties,
    COUNT(DISTINCT t.id) AS total_tenants,
    COUNT(DISTINCT i.id) AS pending_inquiries,
    COUNT(DISTINCT m.id) AS pending_maintenance,
    SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS pending_payments
FROM owner_profiles op
LEFT JOIN boarding_houses bh ON op.id = bh.owner_profile_id
LEFT JOIN room_categories rc ON bh.id = rc.boarding_house_id
LEFT JOIN rooms r ON rc.id = r.room_category_id
LEFT JOIN tenancy_records t ON r.id = t.room_id AND t.status = 'active'
LEFT JOIN inquiries i ON bh.id = i.boarding_house_id AND i.status = 'pending'
LEFT JOIN maintenance_requests m ON r.id = m.room_id AND m.status IN ('pending', 'assigned')
LEFT JOIN payments p ON t.id = p.tenancy_id AND p.status = 'pending'
GROUP BY op.user_id, op.id;

-- 8) STORED PROCEDURE
DELIMITER $$
CREATE PROCEDURE sp_find_nearby(
    IN p_latitude DECIMAL(10,8),
    IN p_longitude DECIMAL(11,8),
    IN p_radius_km INT
)
BEGIN
    SELECT
        bh.*,
        (6371 * ACOS(
            COS(RADIANS(p_latitude)) * COS(RADIANS(bh.latitude)) *
            COS(RADIANS(bh.longitude) - RADIANS(p_longitude)) +
            SIN(RADIANS(p_latitude)) * SIN(RADIANS(bh.latitude))
        )) AS distance
    FROM boarding_houses bh
    WHERE bh.status = 'approved'
    HAVING distance <= p_radius_km
    ORDER BY distance;
END$$
DELIMITER ;
