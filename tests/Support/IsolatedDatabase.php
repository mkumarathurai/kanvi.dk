<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The concurrency tests start real parallel PHP processes, so they need a database
 * of their own rather than the transaction the rest of the suite rolls back.
 *
 * It follows whichever connection the suite is running on, so the same integrity
 * guarantees are exercised against SQLite locally and against MySQL in CI. The
 * engines differ in how they serialise these writes: SQLite uses IMMEDIATE
 * transactions with a busy timeout, MySQL locks the poll row.
 */
trait IsolatedDatabase
{
    private ?string $isolatedSqliteFile = null;

    private ?string $isolatedMysqlDatabase = null;

    private function isolatedDatabaseEnvironment(): array
    {
        $shared = ['APP_ENV' => 'testing', 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];

        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->isolatedSqliteFile = tempnam(sys_get_temp_dir(), 'kanvi-concurrency-');

            return [...$shared, 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $this->isolatedSqliteFile];
        }

        $connection = config('database.connections.mysql');
        $this->isolatedMysqlDatabase = 'kanvi_concurrency_'.Str::lower(Str::random(16));
        DB::statement('CREATE DATABASE `'.$this->isolatedMysqlDatabase.'`');

        return [...$shared,
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => $this->isolatedMysqlDatabase,
            'DB_HOST' => (string) $connection['host'],
            'DB_PORT' => (string) $connection['port'],
            'DB_USERNAME' => (string) $connection['username'],
            'DB_PASSWORD' => (string) $connection['password'],
        ];
    }

    private function dropIsolatedDatabase(): void
    {
        if ($this->isolatedSqliteFile) {
            foreach (['', '-wal', '-shm', '-journal'] as $suffix) {
                if (is_file($this->isolatedSqliteFile.$suffix)) {
                    unlink($this->isolatedSqliteFile.$suffix);
                }
            }
        }
        if ($this->isolatedMysqlDatabase) {
            DB::statement('DROP DATABASE IF EXISTS `'.$this->isolatedMysqlDatabase.'`');
        }
    }
}
