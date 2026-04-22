<?php

namespace App\DTO;

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
}
