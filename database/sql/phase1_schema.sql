-- Phase 1 Schema (Boarding House Management and Comparison System with Geotagging)
-- Stack target: Laravel + MySQL
-- This file is a reference schema for Phase 1 deliverables.

USE geoboard_db;

-- 1) USERS + ROLES (auth + authorization foundation)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM(
        'superduperadmin',
        'admin',
        'owner',
        'manager',
        'tenant',
        'user',
        'caretaker',
        'osas',
        'resident'
    ) NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) BOARDING HOUSES (core listing)
CREATE TABLE IF NOT EXISTS boarding_houses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    description TEXT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    price DECIMAL(10,2) NULL,
    available_rooms INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft','pending','approved','rejected','suspended','closed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_boarding_house_status (status),
    INDEX idx_boarding_house_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) ROOMS
CREATE TABLE IF NOT EXISTS rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    room_no VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    available_slots INT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('Available','Occupied','Reserved','Maintenance') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_rooms_boarding_house (boarding_house_id),
    INDEX idx_rooms_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) AMENITIES + PIVOT
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
    UNIQUE KEY uq_boarding_house_amenity (boarding_house_id, amenity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) BOARDING HOUSE IMAGES
CREATE TABLE IF NOT EXISTS boarding_house_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_label VARCHAR(100) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_boarding_house_images_house (boarding_house_id),
    INDEX idx_boarding_house_images_primary (boarding_house_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) APPROVALS (listing lifecycle audit)
CREATE TABLE IF NOT EXISTS approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    boarding_house_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    decision ENUM('approved','rejected') NOT NULL,
    remarks TEXT NULL,
    reviewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_approvals_house (boarding_house_id),
    INDEX idx_approvals_reviewer (reviewer_id),
    INDEX idx_approvals_decision (decision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) FOREIGN KEYS
ALTER TABLE boarding_houses
    ADD CONSTRAINT fk_boarding_houses_owner
    FOREIGN KEY (owner_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

ALTER TABLE rooms
    ADD CONSTRAINT fk_rooms_boarding_house
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE boarding_house_amenities
    ADD CONSTRAINT fk_bh_amenities_house
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    ADD CONSTRAINT fk_bh_amenities_amenity
    FOREIGN KEY (amenity_id) REFERENCES amenities(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE boarding_house_images
    ADD CONSTRAINT fk_bh_images_house
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE approvals
    ADD CONSTRAINT fk_approvals_house
    FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    ADD CONSTRAINT fk_approvals_reviewer
    FOREIGN KEY (reviewer_id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
