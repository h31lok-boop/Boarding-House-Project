<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $snapshot = $this->loadSnapshot();
        $tables = $snapshot['tables'] ?? null;
        $assets = $snapshot['assets'] ?? [];

        if (! is_array($tables)) {
            throw new RuntimeException('The database seed snapshot does not contain a valid tables section.');
        }

        $missingTables = array_values(array_filter(
            array_keys($tables),
            fn (string $table): bool => ! Schema::hasTable($table),
        ));

        if ($missingTables !== []) {
            throw new RuntimeException(
                'Run all migrations before seeding. Missing tables: '.implode(', ', $missingTables),
            );
        }

        $this->validateAssetBundle($assets);

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($tables): void {
                foreach (array_reverse(array_keys($tables)) as $table) {
                    DB::table($table)->delete();
                }

                foreach ($tables as $table => $rows) {
                    foreach (array_chunk($rows, 100) as $chunk) {
                        if ($chunk !== []) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $assetCount = $this->restorePublicAssets($assets);
        $this->ensurePublicStorageLink();
        $rowCount = array_sum(array_map('count', $tables));

        $this->command?->info(sprintf(
            'Restored %d rows across %d application tables and %d public assets.',
            $rowCount,
            count($tables),
            $assetCount,
        ));
    }

    private function loadSnapshot(): array
    {
        $path = database_path('seeders/data/boarding-housemanagement.json');

        if (! File::isFile($path)) {
            throw new RuntimeException("Database seed snapshot not found at {$path}.");
        }

        return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
    }

    private function restorePublicAssets(array $manifest): int
    {
        $sourceRoot = database_path('seeders/assets/public');
        $disk = Storage::disk('public');
        $restored = 0;

        foreach ($manifest as $relativePath => $metadata) {
            $source = $sourceRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            $stream = fopen($source, 'rb');

            if ($stream === false) {
                throw new RuntimeException("Seed asset could not be opened: {$relativePath}");
            }

            try {
                if (! $disk->put($relativePath, $stream)) {
                    throw new RuntimeException("Seed asset could not be restored: {$relativePath}");
                }
            } finally {
                fclose($stream);
            }

            $restored++;
        }

        return $restored;
    }

    private function validateAssetBundle(array $manifest): void
    {
        $sourceRoot = database_path('seeders/assets/public');

        foreach ($manifest as $relativePath => $metadata) {
            if (
                ! is_string($relativePath)
                || $relativePath === ''
                || str_starts_with($relativePath, '/')
                || preg_match('/(^|\/)\.\.(\/|$)/', $relativePath) === 1
            ) {
                throw new RuntimeException('The seed asset manifest contains an unsafe path.');
            }

            $source = $sourceRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (! File::isFile($source)) {
                throw new RuntimeException("Seed asset is missing: {$relativePath}");
            }

            $expectedHash = $metadata['sha256'] ?? null;

            if (! is_string($expectedHash) || ! hash_equals($expectedHash, hash_file('sha256', $source))) {
                throw new RuntimeException("Seed asset checksum failed: {$relativePath}");
            }
        }
    }

    private function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');

        if (File::exists($link) || is_link($link)) {
            return;
        }

        File::link(storage_path('app/public'), $link);

        if (! File::exists($link) && ! is_link($link)) {
            $this->command?->warn('Public storage could not be linked automatically. Run: php artisan storage:link');
        }
    }
}
