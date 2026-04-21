<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        set_error_handler(function ($severity, $message, $file, $line) {
            if (str_contains($message, 'oci_') || str_contains($message, 'ORA-')) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }

            return false;
        });

        app('Illuminate\Contracts\Debug\ExceptionHandler')->reportable(function (\Throwable $e) {
            if (
                str_contains($e->getMessage(), 'oci_') ||
                str_contains($e->getMessage(), 'ORA-')
            ) {
                Log::warning('Oracle connection error: ' . $e->getMessage());

                return false;
            }
        });


        /**
        * 3. Global Oracle guard (singleton)
        */
        app()->singleton('oracle.guard', function () {
            return new class {
                protected bool $down = false;

                public function isAvailable(): bool {
                    if ($this->down) {
                        return false;
                    }

                    try {
                        DB::connection('oracle')->getPdo();

                        return true;
                    } catch (\Throwable $e) {
                        Log::warning('Oracle DOWN: ' . $e->getMessage());

                        $this->down = true;

                        return false;
                    }
                }
            };
        });
    }
}
