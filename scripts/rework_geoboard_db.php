#!/usr/bin/env php
<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

$projectRoot = dirname(__DIR__);
$defaultSqlFile = $projectRoot.'/database/sql/geoboard_rework.sql';
$sqlFile = $argv[1] ?? $defaultSqlFile;

if (! is_file($sqlFile)) {
    fwrite(STDERR, "SQL file not found: {$sqlFile}".PHP_EOL);
    exit(1);
}

Dotenv::createImmutable($projectRoot)->safeLoad();

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = (int) ($_ENV['DB_PORT'] ?? 3306);
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connection = new mysqli($dbHost, $dbUser, $dbPass, '', $dbPort);
    $connection->set_charset('utf8mb4');
} catch (Throwable $e) {
    fwrite(STDERR, "Failed to connect to MySQL: {$e->getMessage()}".PHP_EOL);
    exit(1);
}

$statements = parseSqlStatements($sqlFile);

if ($statements === []) {
    fwrite(STDERR, 'No SQL statements found in file.'.PHP_EOL);
    exit(1);
}

echo 'Importing SQL from: '.$sqlFile.PHP_EOL;
echo 'Statements to execute: '.count($statements).PHP_EOL;

foreach ($statements as $index => $statement) {
    $sequence = $index + 1;
    $label = summarizeStatement($statement);
    echo sprintf('[%d/%d] %s', $sequence, count($statements), $label).PHP_EOL;

    try {
        $connection->query($statement);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Execution failed at statement #'.$sequence.PHP_EOL);
        fwrite(STDERR, 'Statement preview: '.$label.PHP_EOL);
        fwrite(STDERR, 'MySQL error: '.$e->getMessage().PHP_EOL);
        exit(1);
    }
}

echo 'Database rework import completed successfully.'.PHP_EOL;

function parseSqlStatements(string $sqlFile): array
{
    $lines = file($sqlFile, FILE_IGNORE_NEW_LINES);
    $delimiter = ';';
    $buffer = '';
    $statements = [];

    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        if ($trimmedLine === '') {
            continue;
        }

        if (preg_match('/^DELIMITER\s+(.+)$/i', $trimmedLine, $matches) === 1) {
            $delimiter = $matches[1];

            continue;
        }

        if (isSingleLineComment($trimmedLine)) {
            continue;
        }

        $buffer .= $line.PHP_EOL;

        if (! endsWithDelimiter($buffer, $delimiter)) {
            continue;
        }

        $statement = rtrim($buffer);
        $statement = substr($statement, 0, -strlen($delimiter));
        $statement = trim($statement);

        if ($statement !== '') {
            $statements[] = $statement;
        }

        $buffer = '';
    }

    $remaining = trim($buffer);
    if ($remaining !== '') {
        $statements[] = $remaining;
    }

    return $statements;
}

function isSingleLineComment(string $line): bool
{
    return str_starts_with($line, '--')
        || str_starts_with($line, '#');
}

function endsWithDelimiter(string $sql, string $delimiter): bool
{
    if ($delimiter === '') {
        return false;
    }

    $trimmed = rtrim($sql);
    if (strlen($trimmed) < strlen($delimiter)) {
        return false;
    }

    return substr($trimmed, -strlen($delimiter)) === $delimiter;
}

function summarizeStatement(string $statement): string
{
    $singleLine = preg_replace('/\s+/', ' ', trim($statement));
    if ($singleLine === null || $singleLine === '') {
        return '(empty statement)';
    }

    return strlen($singleLine) > 110
        ? substr($singleLine, 0, 107).'...'
        : $singleLine;
}
