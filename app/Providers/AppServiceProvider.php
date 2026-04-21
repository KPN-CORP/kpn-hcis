<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Connection;
// use Yajra\Pdo\Oci8\Oci8Connection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
        * 1. Convert OCI warnings (oci_connect, etc) → Exception
        */
        // set_error_handler(function ($severity, $message, $file, $line) {
        //     if (str_contains($message, 'oci_') || str_contains($message, 'ORA-')) {
        //         Log::warning('Oracle connection error: ' . $message);

        //         return true;
        //     }

        //     return false;
        // });

        /**
        * 3. Global Oracle guard (singleton)
        */
        // app()->singleton('oracle.guard', function () {
        //     return new class {
        //         protected bool $down = false;

        //         public function isAvailable(): bool {
        //             if ($this->down) {
        //                 return false;
        //             }

        //             try {
        //                 DB::connection('oracle')->getPdo();

        //                 return true;
        //             } catch (\Throwable $e) {
        //                 Log::warning('Oracle DOWN: ' . $e->getMessage());

        //                 $this->down = true;

        //                 return false;
        //             }
        //         }
        //     };
        // });

        //
        //

        // Connection::resolverFor('oracle', function ($connection, $database, $prefix, $config) {

        //     return new class($connection, $database, $prefix, $config) extends Oci8Connection {

        //         protected bool $oracleDown = false;
        //         protected ?int $downUntil = null;

        //         protected function safe(callable $callback, $default)
        //         {
        //             // ⛔ skip kalau masih cooldown
        //             if ($this->downUntil && time() < $this->downUntil) {
        //                 return $default;
        //             }

        //             if ($this->oracleDown) {
        //                 return $default;
        //             }

        //             try {
        //                 return $callback();
        //             } catch (\Throwable $e) {
        //                 Log::warning('Oracle error: ' . $e->getMessage());

        //                 $this->oracleDown = true;
        //                 $this->downUntil = time() + 60; // cooldown 1 menit

        //                 return $default;
        //             }
        //         }

        //         // =========================
        //         // READ
        //         // =========================

        //         public function select($query, $bindings = [], $useReadPdo = true)
        //         {
        //             return $this->safe(
        //                 fn() => parent::select($query, $bindings, $useReadPdo),
        //                 []
        //             );
        //         }

        //         public function selectOne($query, $bindings = [], $useReadPdo = true)
        //         {
        //             return $this->safe(
        //                 fn() => parent::selectOne($query, $bindings, $useReadPdo),
        //                 null
        //             );
        //         }

        //         public function cursor($query, $bindings = [], $useReadPdo = true)
        //         {
        //             return $this->safe(
        //                 fn() => parent::cursor($query, $bindings, $useReadPdo),
        //                 collect()
        //             );
        //         }

        //         // =========================
        //         // WRITE
        //         // =========================

        //         public function insert($query, $bindings = [])
        //         {
        //             return $this->safe(
        //                 fn() => parent::insert($query, $bindings),
        //                 false
        //             );
        //         }

        //         public function insertGetId($query, $bindings = [], $sequence = null)
        //         {
        //             return $this->safe(
        //                 fn() => parent::insertGetId($query, $bindings, $sequence),
        //                 null
        //             );
        //         }

        //         public function update($query, $bindings = [])
        //         {
        //             return $this->safe(
        //                 fn() => parent::update($query, $bindings),
        //                 0
        //             );
        //         }

        //         public function delete($query, $bindings = [])
        //         {
        //             return $this->safe(
        //                 fn() => parent::delete($query, $bindings),
        //                 0
        //             );
        //         }

        //         // =========================
        //         // GENERIC
        //         // =========================

        //         public function statement($query, $bindings = [])
        //         {
        //             return $this->safe(
        //                 fn() => parent::statement($query, $bindings),
        //                 false
        //             );
        //         }

        //         public function affectingStatement($query, $bindings = [])
        //         {
        //             return $this->safe(
        //                 fn() => parent::affectingStatement($query, $bindings),
        //                 0
        //             );
        //         }

        //         public function unprepared($query)
        //         {
        //             return $this->safe(
        //                 fn() => parent::unprepared($query),
        //                 false
        //             );
        //         }

        //         // =========================
        //         // TRANSACTION
        //         // =========================

        //         public function beginTransaction()
        //         {
        //             return $this->safe(
        //                 fn() => parent::beginTransaction(),
        //                 false
        //             );
        //         }

        //         public function commit()
        //         {
        //             return $this->safe(
        //                 fn() => parent::commit(),
        //                 false
        //             );
        //         }

        //         public function rollBack($toLevel = null)
        //         {
        //             return $this->safe(
        //                 fn() => parent::rollBack($toLevel),
        //                 false
        //             );
        //         }

        //         // =========================
        //         // PDO (CRITICAL)
        //         // =========================

        //         public function getPdo()
        //         {
        //             return $this->safe(
        //                 fn() => parent::getPdo(),
        //                 null
        //             );
        //         }

        //         public function getReadPdo()
        //         {
        //             return $this->safe(
        //                 fn() => parent::getReadPdo(),
        //                 null
        //             );
        //         }
        //     };
        // });
    }
}
