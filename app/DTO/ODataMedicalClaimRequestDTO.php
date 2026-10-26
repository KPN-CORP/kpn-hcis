<?php

namespace App\DTO;

class ODataMedicalClaimRequestDTO extends BaseDTO {
    public function __construct(
        public string $employee_code,
    ) {}

    public function toUrlEncode(bool $withQuestionMark = false): string
    {
        $employeeCode = str_replace("'", "''", (string) $this->employee_code);

        $query = http_build_query([
            'employee_code' => $employeeCode,
        ], '', '&', PHP_QUERY_RFC3986);

        return ($withQuestionMark ? '?' : '') . $query;
    }
}

// OLD LOGIC

// <?php

// namespace App\DTO;

// class ODataMedicalClaimRequestDTO extends BaseDTO {
//     public function __construct(
//         public string $employee_id,
//     ) {}

//     public function toUrlEncode(bool $withQuestionMark = false): string
//     {
//         $employeeID = str_replace("'", "''", (string) $this->employee_id);

//         $query = http_build_query([
//             '$filter' => "NIK eq '{$employeeID}'",
//             '$format' => 'json',
//         ], '', '&', PHP_QUERY_RFC3986);

//         return ($withQuestionMark ? '?' : '') . $query;
//     }
// }
