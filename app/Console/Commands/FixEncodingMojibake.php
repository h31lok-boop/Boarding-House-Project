<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Throwable;

class FixEncodingMojibake extends Command
{
    protected $signature = 'encoding:fix-mojibake
        {--dry-run : Report matching rows without changing data}
        {--apply-charset : Convert the configured MySQL/MariaDB database and tables to utf8mb4 first}';

    protected $description = 'Repair common UTF-8 mojibake in text columns and optionally convert MySQL tables to utf8mb4.';

    private const CHARSET = 'utf8mb4';

    private const COLLATION = 'utf8mb4_unicode_ci';

    private const REPLACEMENTS = [
        ['label' => 'peso sign', 'bad' => "\u{00E2}\u{201A}\u{00B1}", 'good' => '₱'],
        ['label' => 'en dash', 'bad' => "\u{00E2}\u{20AC}\u{201C}", 'good' => '–'],
        ['label' => 'em dash', 'bad' => "\u{00E2}\u{20AC}\u{201D}", 'good' => '—'],
        ['label' => 'left apostrophe', 'bad' => "\u{00E2}\u{20AC}\u{02DC}", 'good' => '‘'],
        ['label' => 'right apostrophe', 'bad' => "\u{00E2}\u{20AC}\u{2122}", 'good' => '’'],
        ['label' => 'left quote', 'bad' => "\u{00E2}\u{20AC}\u{0153}", 'good' => '“'],
        ['label' => 'right quote', 'bad' => "\u{00E2}\u{20AC}\u{009D}", 'good' => '”'],
        ['label' => 'ellipsis', 'bad' => "\u{00E2}\u{20AC}\u{00A6}", 'good' => '…'],
        ['label' => 'bullet', 'bad' => "\u{00E2}\u{20AC}\u{00A2}", 'good' => '•'],
        ['label' => 'right arrow', 'bad' => "\u{00E2}\u{2020}\u{2019}", 'good' => '→'],
        ['label' => 'north east arrow', 'bad' => "\u{00E2}\u{2020}\u{2014}", 'good' => '↗'],
        ['label' => 'approximately equal', 'bad' => "\u{00E2}\u{2030}\u{02C6}", 'good' => '≈'],
        ['label' => 'less than or equal', 'bad' => "\u{00E2}\u{2030}\u{00A4}", 'good' => '≤'],
        ['label' => 'light horizontal line', 'bad' => "\u{00E2}\u{201D}\u{20AC}", 'good' => '─'],
        ['label' => 'double horizontal line', 'bad' => "\u{00E2}\u{2022}\u{0090}", 'good' => '═'],
        ['label' => 'pi', 'bad' => "\u{00CF}\u{20AC}", 'good' => 'π'],
        ['label' => 'middle dot', 'bad' => "\u{00C2}\u{00B7}", 'good' => '·'],
        ['label' => 'non-breaking space marker', 'bad' => "\u{00C2}\u{00A0}", 'good' => ' '],
        ['label' => 'stray U+00C2 marker', 'bad' => "\u{00C2}", 'good' => ''],
    ];

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('apply-charset')) {
            $this->convertMysqlCharset($connection, $driver, $dryRun);
        }

        $columns = $this->textColumns($connection);

        if ($columns === []) {
            $this->warn('No string or text columns were found to scan.');

            return self::SUCCESS;
        }

        $this->line(($dryRun ? 'Scanning' : 'Repairing').' '.count($columns).' text column(s).');

        $matches = 0;

        foreach ($columns as [$table, $column]) {
            foreach (self::REPLACEMENTS as $replacement) {
                $count = $this->matchingRowCount($connection, $table, $column, $replacement['bad']);

                if ($count === 0) {
                    continue;
                }

                $matches += $count;
                $this->line(sprintf(
                    '%s.%s: %d row(s) contain %s',
                    $table,
                    $column,
                    $count,
                    $replacement['label'],
                ));

                if (! $dryRun) {
                    $this->replaceInColumn($connection, $table, $column, $replacement['bad'], $replacement['good']);
                }
            }
        }

        if ($matches === 0) {
            $this->info('No corrupted text patterns were found.');
        } elseif ($dryRun) {
            $this->info("Dry run complete. {$matches} replacement match(es) found.");
        } else {
            $this->info("Encoding cleanup complete. {$matches} replacement match(es) repaired.");
        }

        return self::SUCCESS;
    }

    private function convertMysqlCharset(Connection $connection, string $driver, bool $dryRun): void
    {
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->warn("Charset conversion skipped: {$driver} is not MySQL/MariaDB.");

            return;
        }

        $database = $connection->getDatabaseName();
        $tables = $this->tableNames($connection);

        if ($dryRun) {
            $this->line(sprintf(
                'Would convert database %s and %d existing table(s) to %s/%s.',
                $database,
                count($tables),
                self::CHARSET,
                self::COLLATION,
            ));

            return;
        }

        $this->line(sprintf('Converting database %s to %s/%s.', $database, self::CHARSET, self::COLLATION));

        $connection->statement(sprintf(
            'ALTER DATABASE %s CHARACTER SET %s COLLATE %s',
            $this->quoteMysqlIdentifier($database),
            self::CHARSET,
            self::COLLATION,
        ));

        foreach ($tables as $table) {
            try {
                $connection->statement(sprintf(
                    'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s',
                    $connection->getQueryGrammar()->wrapTable($table),
                    self::CHARSET,
                    self::COLLATION,
                ));
            } catch (Throwable $exception) {
                $this->warn("Skipped charset conversion for {$table}: {$exception->getMessage()}");
            }
        }
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function textColumns(Connection $connection): array
    {
        $columns = [];
        $schema = $connection->getSchemaBuilder();

        foreach ($this->tableNames($connection) as $tableName) {
            try {
                $tableColumns = $schema->getColumns($tableName);
            } catch (Throwable $exception) {
                $this->warn("Skipped {$tableName}: {$exception->getMessage()}");

                continue;
            }

            foreach ($tableColumns as $column) {
                $columnName = $column['name'] ?? null;

                if (! is_string($columnName) || $columnName === '' || ($column['generation'] ?? null) !== null) {
                    continue;
                }

                if ($this->isStringColumn($column)) {
                    $columns[] = [$tableName, $columnName];
                }
            }
        }

        return $columns;
    }

    /**
     * @return list<string>
     */
    private function tableNames(Connection $connection): array
    {
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();
        $tables = [];

        foreach ($connection->getSchemaBuilder()->getTables() as $table) {
            $name = $table['name'] ?? null;
            $schema = $table['schema'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            if (
                in_array($driver, ['mariadb', 'mysql'], true)
                && is_string($schema)
                && $schema !== ''
                && $schema !== $database
            ) {
                continue;
            }

            $tables[] = $name;
        }

        return $tables;
    }

    /**
     * @param  array{name?: string, type?: string, type_name?: string}  $column
     */
    private function isStringColumn(array $column): bool
    {
        $type = strtolower((string) ($column['type'] ?? ''));
        $typeName = strtolower((string) ($column['type_name'] ?? ''));

        return str_contains($type, 'char')
            || str_contains($type, 'text')
            || str_contains($typeName, 'char')
            || str_contains($typeName, 'text');
    }

    private function matchingRowCount(Connection $connection, string $table, string $column, string $bad): int
    {
        $columnSql = $connection->getQueryGrammar()->wrap($column);
        $whereSql = $this->containsWhereSql($connection, $columnSql);

        $row = $connection->selectOne(sprintf(
            'SELECT COUNT(*) AS aggregate FROM %s WHERE %s',
            $connection->getQueryGrammar()->wrapTable($table),
            $whereSql,
        ), $this->containsBindings($connection, $bad));

        $values = (array) $row;

        return (int) ($values['aggregate'] ?? 0);
    }

    private function replaceInColumn(Connection $connection, string $table, string $column, string $bad, string $good): int
    {
        $columnSql = $connection->getQueryGrammar()->wrap($column);
        $whereSql = $this->containsWhereSql($connection, $columnSql);

        return $connection->update(sprintf(
            'UPDATE %s SET %s = REPLACE(%s, ?, ?) WHERE %s',
            $connection->getQueryGrammar()->wrapTable($table),
            $columnSql,
            $columnSql,
            $whereSql,
        ), array_merge([$bad, $good], $this->containsBindings($connection, $bad)));
    }

    private function containsWhereSql(Connection $connection, string $columnSql): string
    {
        return match ($connection->getDriverName()) {
            'mariadb', 'mysql' => "LOCATE(?, CONVERT({$columnSql} USING utf8mb4) COLLATE utf8mb4_bin) > 0",
            'pgsql' => "POSITION(? IN {$columnSql}) > 0",
            'sqlite' => "INSTR({$columnSql}, ?) > 0",
            'sqlsrv' => "CHARINDEX(?, {$columnSql}) > 0",
            default => "{$columnSql} LIKE ?",
        };
    }

    /**
     * @return list<string>
     */
    private function containsBindings(Connection $connection, string $bad): array
    {
        return match ($connection->getDriverName()) {
            'mariadb', 'mysql', 'pgsql', 'sqlite', 'sqlsrv' => [$bad],
            default => ['%'.$bad.'%'],
        };
    }

    private function quoteMysqlIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
