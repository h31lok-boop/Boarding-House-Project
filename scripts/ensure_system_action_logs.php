#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

$projectRoot = dirname(__DIR__);
Dotenv::createImmutable($projectRoot)->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$database = $_ENV['DB_DATABASE'] ?? 'geoboard_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $pass, $database, $port);
$db->set_charset('utf8mb4');

$db->query(
    'CREATE TABLE IF NOT EXISTS `system_action_logs` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `actor_id` BIGINT UNSIGNED NULL,
        `action` VARCHAR(80) NOT NULL,
        `entity_type` VARCHAR(80) NOT NULL,
        `entity_id` BIGINT UNSIGNED NULL,
        `context_json` JSON NULL,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_system_action_actor` (`actor_id`),
        INDEX `idx_system_action_entity` (`entity_type`, `entity_id`),
        INDEX `idx_system_action_action` (`action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

echo "system_action_logs table ensured in {$database}".PHP_EOL;
