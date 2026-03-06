USE geoboard_db;

ALTER TABLE boarding_houses
    ADD COLUMN owner_id BIGINT UNSIGNED NULL,
    ADD COLUMN address VARCHAR(255) NULL,
    ADD COLUMN capacity INT UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN approval_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    ADD COLUMN landlord_info VARCHAR(255) NULL,
    ADD COLUMN monthly_payment VARCHAR(255) NULL,
    ADD COLUMN exterior_image VARCHAR(255) NULL,
    ADD COLUMN room_image VARCHAR(255) NULL,
    ADD COLUMN cr_image VARCHAR(255) NULL,
    ADD COLUMN kitchen_image VARCHAR(255) NULL,
    ADD COLUMN contact_name VARCHAR(255) NULL,
    ADD COLUMN contact_phone VARCHAR(255) NULL;

UPDATE boarding_houses
SET address = COALESCE(NULLIF(address, ''), full_address)
WHERE address IS NULL OR address = '';

UPDATE boarding_houses
SET approval_status = CASE LOWER(COALESCE(status, ''))
    WHEN 'approved' THEN 'approved'
    WHEN 'pending' THEN 'pending'
    WHEN 'rejected' THEN 'rejected'
    ELSE 'pending'
END;

ALTER TABLE rooms
    ADD COLUMN boarding_house_id BIGINT UNSIGNED NULL,
    ADD COLUMN room_no VARCHAR(255) NULL,
    ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN description TEXT NULL,
    ADD COLUMN image VARCHAR(255) NULL,
    ADD COLUMN name VARCHAR(255) NULL,
    ADD COLUMN capacity INT UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN available_slots INT UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN amenities TEXT NULL,
    ADD COLUMN image_url VARCHAR(255) NULL;

ALTER TABLE rooms
    MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Available';

UPDATE rooms r
INNER JOIN room_categories rc ON rc.id = r.room_category_id
SET r.boarding_house_id = rc.boarding_house_id
WHERE r.boarding_house_id IS NULL;

UPDATE rooms
SET room_no = COALESCE(NULLIF(room_no, ''), room_number)
WHERE room_no IS NULL OR room_no = '';

UPDATE rooms
SET status = CASE LOWER(COALESCE(status, ''))
    WHEN 'available' THEN 'Available'
    WHEN 'occupied' THEN 'Occupied'
    WHEN 'reserved' THEN 'Reserved'
    WHEN 'maintenance' THEN 'Maintenance'
    WHEN 'cleaning' THEN 'Maintenance'
    ELSE 'Available'
END;

UPDATE rooms r
INNER JOIN room_categories rc ON rc.id = r.room_category_id
SET r.price = CASE WHEN r.price = 0 THEN rc.monthly_rate ELSE r.price END;

CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    start_date DATE NULL,
    end_date DATE NULL,
    notes TEXT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(50) NULL,
    payment_method VARCHAR(50) NULL,
    confirmed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS boarding_house_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS osas_validations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS incidents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    severity VARCHAR(50) NOT NULL DEFAULT 'Low',
    status VARCHAR(50) NOT NULL DEFAULT 'Open',
    reported_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NULL,
    audience VARCHAR(255) NOT NULL DEFAULT 'All Tenants',
    status VARCHAR(50) NOT NULL DEFAULT 'Open',
    scheduled_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS validation_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    validator_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'assigned',
    scheduled_at DATE NULL,
    priority VARCHAR(50) NOT NULL DEFAULT 'Normal',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS validation_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    validation_task_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'draft',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS validation_findings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    severity VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS validation_evidence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    path VARCHAR(255) NOT NULL,
    type VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accreditations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    decision_log TEXT NULL,
    effective_from DATE NULL,
    effective_to DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    landmark VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS amenities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS boarding_house_amenities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    amenity_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY boarding_house_amenities_unique (boarding_house_id, amenity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NULL,
    move_in_date DATE NULL,
    move_out_date DATE NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NULL,
    check_in_date DATE NULL,
    check_out_date DATE NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE payments
    ADD COLUMN tenant_id BIGINT UNSIGNED NULL,
    ADD COLUMN boarding_house_id BIGINT UNSIGNED NULL,
    ADD COLUMN paid_at DATETIME NULL,
    ADD COLUMN reference_no VARCHAR(191) NULL;

ALTER TABLE favorites
    ADD COLUMN user_id BIGINT UNSIGNED NULL;

UPDATE favorites f
INNER JOIN tenant_profiles tp ON tp.id = f.tenant_profile_id
SET f.user_id = tp.user_id
WHERE f.user_id IS NULL;

ALTER TABLE inquiries
    ADD COLUMN user_id BIGINT UNSIGNED NULL,
    ADD COLUMN replied_at DATETIME NULL;

UPDATE inquiries i
INNER JOIN tenant_profiles tp ON tp.id = i.tenant_profile_id
SET i.user_id = tp.user_id
WHERE i.user_id IS NULL;

ALTER TABLE reviews
    ADD COLUMN user_id BIGINT UNSIGNED NULL,
    ADD COLUMN rating TINYINT UNSIGNED NULL;

UPDATE reviews r
INNER JOIN tenant_profiles tp ON tp.id = r.tenant_profile_id
SET r.user_id = tp.user_id
WHERE r.user_id IS NULL;

UPDATE reviews
SET rating = overall_rating
WHERE rating IS NULL;

ALTER TABLE maintenance_requests
    ADD COLUMN user_id BIGINT UNSIGNED NULL,
    ADD COLUMN issue VARCHAR(255) NULL,
    ADD COLUMN resolved_at TIMESTAMP NULL;

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('admin', 'web', NOW(), NOW()),
('tenant', 'web', NOW(), NOW()),
('caretaker', 'web', NOW(), NOW()),
('osas', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r
JOIN users u ON u.email = 'admin@geoboard.com'
WHERE r.name = 'admin' AND r.guard_name = 'web';
