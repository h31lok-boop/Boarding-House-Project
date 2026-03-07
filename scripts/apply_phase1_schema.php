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

echo "Applying Phase 1 schema patch to {$database}...".PHP_EOL;

runQuery(
    $db,
    'CREATE TABLE IF NOT EXISTS `boarding_house_images` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `boarding_house_id` BIGINT UNSIGNED NOT NULL,
        `image_path` VARCHAR(500) NOT NULL,
        `image_label` VARCHAR(100) NULL,
        `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
        `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_bh_images_house` (`boarding_house_id`),
        INDEX `idx_bh_images_primary` (`boarding_house_id`, `is_primary`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'ensure boarding_house_images table'
);

runQuery(
    $db,
    "CREATE TABLE IF NOT EXISTS `approvals` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `boarding_house_id` BIGINT UNSIGNED NOT NULL,
        `reviewer_id` BIGINT UNSIGNED NOT NULL,
        `decision` ENUM('approved', 'rejected') NOT NULL,
        `remarks` TEXT NULL,
        `reviewed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_approvals_house` (`boarding_house_id`),
        INDEX `idx_approvals_reviewer` (`reviewer_id`),
        INDEX `idx_approvals_decision` (`decision`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'ensure approvals table'
);

addColumnIfMissing($db, 'boarding_houses', 'price', 'DECIMAL(10,2) NULL');
addColumnIfMissing($db, 'boarding_houses', 'available_rooms', 'INT UNSIGNED NOT NULL DEFAULT 0');

if (columnExists($db, 'users', 'role')) {
    $roleColumn = fetchOne(
        $db,
        "SELECT COLUMN_TYPE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'users'
           AND COLUMN_NAME = 'role'
         LIMIT 1"
    );

    $columnType = strtolower((string) ($roleColumn['COLUMN_TYPE'] ?? ''));
    if (! str_contains($columnType, "'superduperadmin'") || ! str_contains($columnType, "'user'")) {
        runQuery(
            $db,
            "ALTER TABLE `users`
             MODIFY COLUMN `role`
             ENUM(
                'superduperadmin',
                'admin',
                'owner',
                'manager',
                'tenant',
                'user',
                'caretaker',
                'osas',
                'resident'
             ) NOT NULL DEFAULT 'tenant'",
            'extend users.role enum for phase1'
        );
    }
}

if (columnExists($db, 'boarding_houses', 'monthly_payment')) {
    runQuery(
        $db,
        "UPDATE `boarding_houses`
         SET `price` = CAST(
                NULLIF(
                    REPLACE(REPLACE(REPLACE(`monthly_payment`, 'PHP', ''), ',', ''), ' ', ''),
                    ''
                ) AS DECIMAL(10,2)
             )
         WHERE (`price` IS NULL OR `price` = 0)
           AND `monthly_payment` IS NOT NULL",
        'backfill boarding_houses.price from monthly_payment'
    );
}

if (tableExists($db, 'rooms') && columnExists($db, 'rooms', 'boarding_house_id')) {
    runQuery(
        $db,
        "UPDATE `boarding_houses` bh
         LEFT JOIN (
            SELECT `boarding_house_id`, COUNT(*) AS available_count
            FROM `rooms`
            WHERE LOWER(`status`) = 'available'
            GROUP BY `boarding_house_id`
         ) r ON r.boarding_house_id = bh.id
         SET bh.available_rooms = COALESCE(r.available_count, 0)",
        'backfill boarding_houses.available_rooms from rooms'
    );
}

if (tableExists($db, 'users') && tableExists($db, 'boarding_houses')) {
    addForeignKeyIfMissing(
        $db,
        'boarding_houses',
        'fk_phase1_boarding_houses_owner',
        'owner_id',
        'users',
        'id',
        'SET NULL'
    );
}

if (tableExists($db, 'boarding_houses') && tableExists($db, 'boarding_house_images')) {
    addForeignKeyIfMissing(
        $db,
        'boarding_house_images',
        'fk_phase1_bh_images_house',
        'boarding_house_id',
        'boarding_houses',
        'id',
        'CASCADE'
    );
}

if (tableExists($db, 'boarding_houses') && tableExists($db, 'approvals')) {
    addForeignKeyIfMissing(
        $db,
        'approvals',
        'fk_phase1_approvals_house',
        'boarding_house_id',
        'boarding_houses',
        'id',
        'CASCADE'
    );
}

if (tableExists($db, 'users') && tableExists($db, 'approvals')) {
    addForeignKeyIfMissing(
        $db,
        'approvals',
        'fk_phase1_approvals_reviewer',
        'reviewer_id',
        'users',
        'id',
        'CASCADE'
    );
}

echo 'Phase 1 schema patch completed.'.PHP_EOL;

function runQuery(mysqli $db, string $sql, string $label): void
{
    $db->query($sql);
    echo "OK: {$label}".PHP_EOL;
}

function fetchOne(mysqli $db, string $sql): array
{
    $result = $db->query($sql);
    $row = $result->fetch_assoc();

    return $row ?: [];
}

function tableExists(mysqli $db, string $table): bool
{
    $table = $db->real_escape_string($table);
    $row = fetchOne(
        $db,
        "SELECT COUNT(*) AS c
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$table}'"
    );

    return ((int) ($row['c'] ?? 0)) > 0;
}

function columnExists(mysqli $db, string $table, string $column): bool
{
    $table = $db->real_escape_string($table);
    $column = $db->real_escape_string($column);
    $row = fetchOne(
        $db,
        "SELECT COUNT(*) AS c
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$table}'
           AND COLUMN_NAME = '{$column}'"
    );

    return ((int) ($row['c'] ?? 0)) > 0;
}

function addColumnIfMissing(mysqli $db, string $table, string $column, string $definition): void
{
    if (columnExists($db, $table, $column)) {
        return;
    }

    runQuery(
        $db,
        "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}",
        "add {$table}.{$column}"
    );
}

function constraintExists(mysqli $db, string $table, string $constraint): bool
{
    $table = $db->real_escape_string($table);
    $constraint = $db->real_escape_string($constraint);
    $row = fetchOne(
        $db,
        "SELECT COUNT(*) AS c
         FROM information_schema.TABLE_CONSTRAINTS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$table}'
           AND CONSTRAINT_NAME = '{$constraint}'"
    );

    return ((int) ($row['c'] ?? 0)) > 0;
}

function addForeignKeyIfMissing(
    mysqli $db,
    string $table,
    string $constraintName,
    string $column,
    string $referenceTable,
    string $referenceColumn,
    string $onDelete = 'SET NULL'
): void {
    if (! columnExists($db, $table, $column) || constraintExists($db, $table, $constraintName)) {
        return;
    }

    runQuery(
        $db,
        "ALTER TABLE `{$table}`
         ADD CONSTRAINT `{$constraintName}`
         FOREIGN KEY (`{$column}`) REFERENCES `{$referenceTable}`(`{$referenceColumn}`)
         ON DELETE {$onDelete}
         ON UPDATE CASCADE",
        "add foreign key {$constraintName}"
    );
}
