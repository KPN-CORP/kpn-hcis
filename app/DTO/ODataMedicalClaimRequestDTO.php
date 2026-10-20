<?php

namespace App\DTO;

class ODataMedicalClaimRequestDTO extends BaseDTO {
    public function __construct(
        public string $employee_id,
    ) {}

    public function toUrlEncode(bool $withQuestionMark = false): string {
        $employeeID = $this->employee_id;

        $query = "$filter=(NIK eq '{$employeeID}')&$format=json";

        return ($withQuestionMark ? '?' : '') . $query;
    }
}
