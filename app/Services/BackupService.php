<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Database backups stored privately in storage/app/private/backups.
 * Uses a pure-PHP SQL dump so it works on shared hosting without mysqldump.
 */
class BackupService
{
    public const DISK = 'local';
    public const DIR = 'backups';

    public function create(): string
    {
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Backups are supported for MySQL/MariaDB only.');
        }

        $pdo = DB::connection()->getPdo();
        $sql = "-- ".site_name()." database backup\n-- Created: ".now()->toDateTimeString()."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = array_map(fn ($row) => array_values((array) $row)[0], DB::select('SHOW TABLES'));

        foreach ($tables as $table) {
            $create = (array) DB::selectOne("SHOW CREATE TABLE `$table`");
            $sql .= "DROP TABLE IF EXISTS `$table`;\n".array_values($create)[1].";\n\n";

            foreach (DB::table($table)->cursor() as $row) {
                $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $row);
                $sql .= "INSERT INTO `$table` VALUES (".implode(',', $values).");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $name = 'backup-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(6)).'.sql.gz';
        Storage::disk(self::DISK)->put(self::DIR.'/'.$name, gzencode($sql, 9));

        return $name;
    }

    /** @return list<array{name: string, size: int, date: int}> */
    public function list(): array
    {
        $disk = Storage::disk(self::DISK);

        return collect($disk->files(self::DIR))
            ->filter(fn ($f) => str_ends_with($f, '.sql.gz'))
            ->map(fn ($f) => ['name' => basename($f), 'size' => $disk->size($f), 'date' => $disk->lastModified($f)])
            ->sortByDesc('date')->values()->all();
    }

    public function path(string $name): string
    {
        // basename() prevents path traversal such as "../../.env"
        $file = self::DIR.'/'.basename($name);
        if (! str_ends_with($file, '.sql.gz') || ! Storage::disk(self::DISK)->exists($file)) {
            abort(404);
        }

        return $file;
    }

    public function delete(string $name): void
    {
        Storage::disk(self::DISK)->delete($this->path($name));
    }
}
