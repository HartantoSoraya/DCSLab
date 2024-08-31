<?php

namespace App\Rules;

use App\Enums\RecordStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchUpdateValidStatus implements ValidationRule
{
    protected ?bool $isMain;

    public function __construct(?bool $isMain)
    {
        $this->isMain = $isMain;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->isMain == true && $value == RecordStatus::INACTIVE->value) {
            $fail('rules.branch.set_branch_to_non_main')->translate();

            return;
        }
    }
}
