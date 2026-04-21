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
        if (!app('oracle.guard')->isAvailable()) {
            return $default;
        }

        try {
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

    protected function eagerLoadRelations(array $models)
    {
        if (!app('oracle.guard')->isAvailable()) {
            return $this->setEmptyRelations($models);
        }

        try {
            return parent::eagerLoadRelations($models);
        } catch (\Throwable $e) {
            Log::warning('Oracle eager load failed: ' . $e->getMessage());

            return $this->setEmptyRelations($models);
        }
    }

    protected function setEmptyRelations(array $models)
    {
        foreach ($models as $model) {
            foreach ($this->safeWith as $relation => $constraints) {
                $relationName = is_numeric($relation) ? $constraints : $relation;

                try {
                    $relationObj = $model->$relationName();

                    if (method_exists($relationObj, 'getResults')) {
                        $result = $relationObj->getResults();

                        $fallback = $result instanceof \Illuminate\Support\Collection
                            ? collect()
                            : null;
                    } else {
                        $fallback = null;
                    }
                } catch (\Throwable $e) {
                    $fallback = null;
                }

                $model->setRelation($relationName, $fallback);
            }
        }

        return $models;
    }
}
