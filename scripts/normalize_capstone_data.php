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

$updates = [
    "UPDATE boarding_houses
     SET price = CAST(NULLIF(REPLACE(REPLACE(REPLACE(monthly_payment, 'PHP', ''), ',', ''), ' ', ''), '') AS DECIMAL(10,2))
     WHERE (price IS NULL OR price = 0) AND monthly_payment IS NOT NULL",
    "UPDATE boarding_houses
     SET monthly_payment = CAST(price AS CHAR)
     WHERE (monthly_payment IS NULL OR monthly_payment = '') AND price IS NOT NULL",
    "UPDATE boarding_houses
     SET contact_number = contact_phone
     WHERE (contact_number IS NULL OR contact_number = '') AND contact_phone IS NOT NULL",
    "UPDATE boarding_houses
     SET contact_phone = contact_number
     WHERE (contact_phone IS NULL OR contact_phone = '') AND contact_number IS NOT NULL",
    "UPDATE rooms
     SET room_no = room_number
     WHERE (room_no IS NULL OR room_no = '') AND room_number IS NOT NULL",
    "UPDATE rooms
     SET room_number = room_no
     WHERE (room_number IS NULL OR room_number = '') AND room_no IS NOT NULL",
];

foreach ($updates as $sql) {
    $db->query($sql);
}

echo "Data normalization finished for {$database}".PHP_EOL;
