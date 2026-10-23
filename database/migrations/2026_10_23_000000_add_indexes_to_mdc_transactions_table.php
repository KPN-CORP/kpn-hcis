<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * mdc_transactions only had its PRIMARY KEY on usage_id. Every lookup by
 * no_medic, employee_id or status was a full scan of ~43k rows, which is why
 * /medical/confirmation spent ~25s inside one per-row query.
 *
 * EXPLAIN before:  type=ALL  key=NULL  rows=42268
 */
return new class extends Migration
{
    /**
     * Index name => column.
     */
    private array $indexes = [
        'mdc_transactions_no_medic_index' => 'no_medic',
        'mdc_transactions_employee_id_index' => 'employee_id',
        'mdc_transactions_status_index' => 'status',
        'mdc_transactions_deleted_at_index' => 'deleted_at',
    ];

    public function up(): void
    {
        Schema::table('mdc_transactions', function (Blueprint $table) {
            foreach ($this->indexes as $name => $column) {
                if (! $this->indexExists($name)) {
                    $table->index($column, $name);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('mdc_transactions', function (Blueprint $table) {
            foreach ($this->indexes as $name => $column) {
                if ($this->indexExists($name)) {
                    $table->dropIndex($name);
                }
            }
        });
    }

    private function indexExists(string $name): bool
    {
        return ! empty(DB::select(
            'SHOW INDEX FROM `mdc_transactions` WHERE Key_name = ?',
            [$name]
        ));
    }
};
