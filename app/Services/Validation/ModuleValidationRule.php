<?php

namespace App\Services\Validation;

interface ModuleValidationRule
{
    /**
     * Get the descriptive name of the validation check.
     */
    public function name(): string;

    /**
     * Get the weight of the validation rule (used for scoring compliance).
     */
    public function weight(): int;

    /**
     * Execute the validation rule against the module context.
     */
    public function validate(ModuleContext $context): ValidationResult;
}
