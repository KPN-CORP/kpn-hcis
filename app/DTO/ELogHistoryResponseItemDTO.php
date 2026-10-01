<?php

namespace App\DTO;

class ELogHistoryResponseItemDTO extends BaseDTO {
    public function __construct(
        public readonly string $action_type,
        public readonly int $pos,
        public readonly int $data_status,
        public readonly string $remark,
        public readonly string $created_by,
        public readonly string $created_at,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            action_type: $data['ACTION_TYPE'] ?? '',
            pos: (int) ($data['POS'] ?? 0),
            data_status: (int) ($data['STATUS'] ?? 0),
            remark: $data['REMARK'] ?? '',
            created_by: $data['CREATED_BY'] ?? '',
            created_at: $data['CREATED_AT'] ?? '',
        );
    }
}
