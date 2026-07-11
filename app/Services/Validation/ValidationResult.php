<?php

namespace App\Services\Validation;

class ValidationResult
{
    private string $ruleName;
    private array $logs = [];
    private bool $passed = true;

    public function __construct(string $ruleName)
    {
        $this->ruleName = $ruleName;
    }

    /**
     * Get the rule name that produced this result.
     */
    public function getRuleName(): string
    {
        return $this->ruleName;
    }

    /**
     * Add an informational message.
     */
    public function addInfo(string $message): void
    {
        $this->logs[] = ['severity' => 'INFO', 'message' => $message];
    }

    /**
     * Add a warning message.
     */
    public function addWarning(string $message): void
    {
        $this->logs[] = ['severity' => 'WARNING', 'message' => $message];
    }

    /**
     * Add an error message (breaks compliance).
     */
    public function addError(string $message): void
    {
        $this->logs[] = ['severity' => 'ERROR', 'message' => $message];
        $this->passed = false;
    }

    /**
     * Add a fatal error message (prevents further check execution of this rule).
     */
    public function addFatal(string $message): void
    {
        $this->logs[] = ['severity' => 'FATAL', 'message' => $message];
        $this->passed = false;
    }

    /**
     * Check if the rule passed successfully.
     */
    public function passed(): bool
    {
        return $this->passed;
    }

    /**
     * Get all logged messages.
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Check if there are any ERROR or FATAL logs.
     */
    public function hasErrors(): bool
    {
        foreach ($this->logs as $log) {
            if ($log['severity'] === 'ERROR' || $log['severity'] === 'FATAL') {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if there are any WARNING logs.
     */
    public function hasWarnings(): bool
    {
        foreach ($this->logs as $log) {
            if ($log['severity'] === 'WARNING') {
                return true;
            }
        }
        return false;
    }
}
