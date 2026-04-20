<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SafeOracleBuilder extends Builder
{
    protected array $safeWith = [];

    public function with($relations) {
        $this->safeWith = is_array($relations) ? $relations : func_get_args();

        return parent::with($relations);
    }

    protected function safe(callable $callback, $default) {
        try {
            $this->getConnection()->getPdo();

            return $callback();
        } catch (\Throwable $e) {
            Log::warning('Oracle builder error: ' . $e->getMessage());

            return $default;
        }
    }

    public function get($columns = ['*']) {
        return $this->safe(function () use ($columns) {
            return parent::get($columns);
        }, collect());
    }

    public function first($columns = ['*']) {
        return $this->safe(function () use ($columns) {
            return parent::first($columns);
        }, null);
    }

    public function count($columns = '*') {
        return $this->safe(function () use ($columns) {
            return parent::count($columns);
        }, 0);
    }

    public function exists() {
        return $this->safe(fn() => parent::exists(), false);
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
        return $this->safe(function () use ($perPage, $columns, $pageName, $page) {
            return parent::paginate($perPage, $columns, $pageName, $page);
        }, collect());
    }

    public function update(array $values) {
        return $this->safe(function () use ($values) {
            return parent::update($values);
        }, 0);
    }

    public function delete() {
        return $this->safe(fn() => parent::delete(), 0);
    }

    protected function eagerLoadRelations(array $models) {
        try {
            return parent::eagerLoadRelations($models);
        } catch (\Throwable $e) {
            Log::warning('Oracle eager load failed: ' . $e->getMessage());

            foreach ($models as $model) {
                foreach (array_keys($this->safeWith) as $relation) {
                    $model->setRelation($relation, collect());
                }
            }

            return $models;
        }
    }
}
