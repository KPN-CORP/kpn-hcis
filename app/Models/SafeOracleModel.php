<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class SafeOracleModel extends Model
{
    protected $connection = 'oracle';

    public function newEloquentBuilder($query) {
        return new SafeOracleBuilder($query);
    }

    public function getRelationValue($key) {
        if (!app('oracle.guard')->isAvailable()) {
            return null;
        }

        try {
            return parent::getRelationValue($key);
        } catch (\Throwable $e) {
            Log::warning("Oracle relation [$key] failed: " . $e->getMessage());

            return null;
        }
    }

    protected function safe(callable $callback, $default = false) {
        if (!app('oracle.guard')->isAvailable()) {
            return $default;
        }

        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('Oracle model error: ' . $e->getMessage());

            return $default;
        }
    }

    public function save(array $options = []) {
        return $this->safe(function () use ($options) {
            return parent::save($options);
        }, false);
    }

    public function delete() {
        return $this->safe(fn() => parent::delete(), false);
    }

    public function update(array $attributes = [], array $options = []) {
        return $this->safe(function () use ($attributes, $options) {
            return parent::update($attributes, $options);
        }, false);
    }

    public function insert(array $attributes) {
        return $this->safe(function () use ($attributes) {
            return parent::insert($attributes);
        }, false);
    }

    public static function create(array $attributes = []) {
        if (!app('oracle.guard')->isAvailable()) {
            return null;
        }

        try {
            return parent::create($attributes);
        } catch (\Throwable $e) {
            Log::warning('Oracle create failed: ' . $e->getMessage());

            return null;
        }
    }
}
