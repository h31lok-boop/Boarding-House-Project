<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$excludedTables = [
    'cache',
    'cache_locks',
    'failed_jobs',
    'job_batches',
    'jobs',
    'migrations',
    'password_reset_tokens',
    'sessions',
];

$databaseName = DB::connection()->getDatabaseName();
$tableRows = DB::select(
    <<<'SQL'
        SELECT TABLE_NAME AS table_name
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
        ORDER BY TABLE_NAME
        SQL,
    [$databaseName],
);

$tables = [];
$rowCount = 0;

foreach ($tableRows as $tableRow) {
    $table = $tableRow->table_name;

    if (in_array($table, $excludedTables, true)) {
        continue;
    }

    $columnRows = DB::select(
        <<<'SQL'
            SELECT COLUMN_NAME AS column_name, EXTRA AS extra
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
            SQL,
        [$databaseName, $table],
    );

    $insertableColumns = collect($columnRows)
        ->reject(fn (object $column): bool => str_contains(strtoupper($column->extra), 'GENERATED'))
        ->pluck('column_name')
        ->all();

    $primaryKeyRows = DB::select(
        <<<'SQL'
            SELECT COLUMN_NAME AS column_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'
            ORDER BY ORDINAL_POSITION
            SQL,
        [$databaseName, $table],
    );

    $query = DB::table($table)->select($insertableColumns);

    foreach ($primaryKeyRows as $primaryKeyRow) {
        $query->orderBy($primaryKeyRow->column_name);
    }

    $rows = $query->get()
        ->map(fn (object $row): array => (array) $row)
        ->all();

    $tables[$table] = $rows;
    $rowCount += count($rows);
}

$assetSource = Storage::disk('public')->path('');
$assetTarget = database_path('seeders/assets/public');

if (File::isDirectory($assetTarget)) {
    File::deleteDirectory($assetTarget);
}

File::ensureDirectoryExists($assetTarget);

$assets = [];

if (File::isDirectory($assetSource)) {
    foreach (File::allFiles($assetSource) as $file) {
        if ($file->getFilename() === '.gitignore') {
            continue;
        }

        $relativePath = str_replace('\\', '/', $file->getRelativePathname());
        $destination = $assetTarget.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        File::ensureDirectoryExists(dirname($destination));
        File::copy($file->getPathname(), $destination);

        $assets[$relativePath] = [
            'bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getPathname()),
        ];
    }
}

ksort($assets);

$snapshot = [
    'format' => 1,
    'source_database' => $databaseName,
    'excluded_runtime_tables' => $excludedTables,
    'assets' => $assets,
    'tables' => $tables,
];

$json = json_encode(
    $snapshot,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
).PHP_EOL;

$snapshotPath = database_path('seeders/data/boarding-housemanagement.json');
File::ensureDirectoryExists(dirname($snapshotPath));
File::put($snapshotPath, $json);

fwrite(STDOUT, sprintf(
    "Exported %d rows from %d application tables and %d public assets (%s) to the portable seed snapshot.\n",
    $rowCount,
    count($tables),
    count($assets),
    number_format(array_sum(array_column($assets, 'bytes'))).' bytes',
));
