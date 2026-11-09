<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$database = DB::connection()->getDatabaseName();
$pdo = DB::connection()->getPdo();
$quote = static fn (string $value): string => '`'.str_replace('`', '``', $value).'`';
$sql = [
    '-- BoardMatch database export',
    '-- Database: '.$database,
    '-- Generated: '.date('c'),
    'SET FOREIGN_KEY_CHECKS=0;',
    'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
    '',
];

$tables = DB::select('SELECT TABLE_NAME AS table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME', [$database]);

foreach ($tables as $tableRow) {
    $table = $tableRow->table_name;
    $quotedTable = $quote($table);
    $create = DB::selectOne("SHOW CREATE TABLE {$quotedTable}");
    $createSql = $create->{'Create Table'} ?? $create->{'Create View'} ?? null;
    if ($createSql === null) {
        continue;
    }

    $sql[] = "DROP TABLE IF EXISTS {$quotedTable};";
    $sql[] = $createSql.';';

    $rows = DB::table($table)->get();
    if ($rows->isEmpty()) {
        $sql[] = '';
        continue;
    }

    $columns = array_keys((array) $rows->first());
    $columnSql = implode(', ', array_map($quote, $columns));
    foreach ($rows->chunk(250) as $chunk) {
        $values = $chunk->map(function (object $row) use ($columns, $pdo): string {
            return '('.implode(', ', array_map(function (string $column) use ($row, $pdo): string {
                $value = $row->{$column};
                return $value === null ? 'NULL' : $pdo->quote((string) $value);
            }, $columns)).')';
        })->implode(",\n");
        $sql[] = "INSERT INTO {$quotedTable} ({$columnSql}) VALUES\n{$values};";
    }
    $sql[] = '';
}

$sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
$path = __DIR__.'/../database/exports/'.$database.'_'.date('Y-m-d_His').'.sql';
if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0777, true);
}
file_put_contents($path, implode("\n", $sql)."\n");
fwrite(STDOUT, "Exported {$database} to {$path}\n");

