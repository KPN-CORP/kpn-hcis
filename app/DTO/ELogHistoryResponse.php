<?php

namespace App\DTO;

class ELogHistoryResponseDTO extends BaseDTO {
    public function __construct(
        public readonly string $status,
        public readonly int $count,
        public readonly string $no_receipt_doc,
        public readonly string $action_type,
        public readonly int $pos,
        public readonly int $data_status,
        public readonly string $remark,
        public readonly string $created_by,
        public readonly string $created_at,
    ) {}

    public static function fromArray(array $response): self {
        return new self(
            status: $response['status'] ?? '',
            no_receipt_doc: $response['no_receipt_doc'] ?? '',
            count: $response['count'] ?? '',

            company: $response['data']['ACTION_TYPE'] ?? '',
            pos: $response['data']['POS'] ?? 0,
            data_status: $response['data']['STATUS'] ?? 0,
            remark: $response['data']['REMARK'] ?? '',
            last_updated_by: $response['data']['CREATED_BY'] ?? '',
            last_updated_at: $response['data']['CREATED_AT'] ?? '',
        );
    }
}


datanya gini:

{
  "status": "success",
  "no_receipt_doc": "HC-AC-2026-04-22-0005",
  "count": 4,
  "data": [
    {
      "ACTION_TYPE": "SUBMIT",
      "POS": 1,
      "STATUS": 0,
      "REMARK": "Dokumen baru dari HCIS",
      "CREATED_BY": "HCIS_SYSTEM",
      "CREATED_AT": "2026-04-22T10:00:00"
    },
    {
      "ACTION_TYPE": "VERIFY",
      "POS": 2,
      "STATUS": 1,
      "REMARK": "Sudah diverifikasi",
      "CREATED_BY": "GA_STAFF",
      "CREATED_AT": "2026-04-22T11:15:00"
    }
  ]
}
