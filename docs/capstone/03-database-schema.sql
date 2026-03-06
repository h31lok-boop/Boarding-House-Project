-- Boarding House Management and Comparison System
-- Core capstone schema (MySQL 8)

CREATE TABLE roles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'tenant',
  phone VARCHAR(50) NULL,
  profile_image VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE boarding_houses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  owner_id BIGINT UNSIGNED NULL,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(500) NOT NULL,
  description TEXT NULL,
  monthly_payment DECIMAL(10,2) NULL,
  capacity INT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  approval_status VARCHAR(20) NOT NULL DEFAULT 'pending',
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  contact_name VARCHAR(255) NULL,
  contact_phone VARCHAR(50) NULL,
  house_rules TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_boarding_house_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE locations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  landmark VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_locations_boarding_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE amenities (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) UNIQUE NOT NULL,
  icon VARCHAR(60) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE boarding_house_amenities (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  amenity_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_house_amenity (boarding_house_id, amenity_id),
  CONSTRAINT fk_bha_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
  CONSTRAINT fk_bha_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

CREATE TABLE rooms (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  room_no VARCHAR(60) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  capacity INT UNSIGNED NOT NULL DEFAULT 1,
  available_slots INT UNSIGNED NOT NULL DEFAULT 1,
  status VARCHAR(20) NOT NULL DEFAULT 'Available',
  description TEXT NULL,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_rooms_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE tenants (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  room_id BIGINT UNSIGNED NULL,
  move_in_date DATE NULL,
  move_out_date DATE NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_tenants_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_tenants_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
  CONSTRAINT fk_tenants_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

CREATE TABLE payments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  due_date DATE NULL,
  paid_at DATETIME NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  reference_no VARCHAR(120) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_payments_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_payments_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_favorite (user_id, boarding_house_id),
  CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_favorites_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE inquiries (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  replied_at DATETIME NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_inquiries_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);

CREATE TABLE reservations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  room_id BIGINT UNSIGNED NULL,
  check_in_date DATE NULL,
  check_out_date DATE NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reservations_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE,
  CONSTRAINT fk_reservations_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

CREATE TABLE reviews (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  boarding_house_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_house FOREIGN KEY (boarding_house_id) REFERENCES boarding_houses(id) ON DELETE CASCADE
);
