<?php

namespace App\DTO;

use ReflectionClass;

abstract class BaseDTO {
    public function toArray(): array {
        return array_filter(
            get_object_vars($this),
            fn ($value) => !is_null($value)
        );
    }

    public function toUrlEncode(bool $withQuestionMark = false): string {
        $query = http_build_query($this->toArray());

        if ($query === '') {
            return '';
        }

        return ($withQuestionMark ? '?' : '') . $query;
    }

    public function toUpperCaseArray(): array
    {
        return collect($this->toArray())
            ->mapWithKeys(fn ($value, $key) => [
                strtoupper($key) => $value
            ])
            ->all();
    }

    public function toJSON(int $options = 0): string {
        $json = json_encode($this->toArray(), $options);

        if ($json === false) {
            return '{}';
        }

        return $json;
    }

    public static function fromArray(array $data): static {
        $reflection = new ReflectionClass(static::class);

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new static();
        }

        $params = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $params[$name] = $data[$name] ?? $parameter->getDefaultValue();
        }

        return new static(...$params);
    }

    protected static function toInt(mixed $value, int $default = 0): int
    {
        if (is_int($value))   return $value;
        if (is_bool($value))  return $default;
        if (is_float($value)) return is_finite($value) ? (int) round($value) : $default;

        if (!is_string($value)) return $default;

        $clean = str_replace(',', '', trim($value));
        $clean = preg_replace('/[^0-9.\-+]/', '', $clean);

        if ($clean === '' || !is_numeric($clean)) return $default;

        return (int) round((float) $clean);
    }
}
