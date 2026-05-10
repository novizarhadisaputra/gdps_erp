<?php

namespace Modules\MasterData\Observers;

use Modules\MasterData\Models\Employee;

class EmployeeObserver
{
    public function saving(Employee $employee): void
    {
        if (empty($employee->code)) {
            $employee->code = $this->generateCode();
        }
    }

    protected function generateCode(): string
    {
        $prefix = 'EMP';
        $latest = Employee::where('code', 'LIKE', $prefix.'-%')
            ->orderBy('code', 'desc')
            ->first();

        if (! $latest) {
            return $prefix.'-001';
        }

        $lastNumber = (int) substr($latest->code, strrpos($latest->code, '-') + 1);
        $nextNumber = str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);

        return $prefix.'-'.$nextNumber;
    }
}
