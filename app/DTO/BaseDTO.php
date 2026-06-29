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
}
