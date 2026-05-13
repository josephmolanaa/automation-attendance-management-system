<?php

namespace App\Services\Translation;

class MigrationResult
{
    /**
     * @var int Number of text replacements made
     */
    public int $replacedCount;

    /**
     * @var int Number of new translation keys created
     */
    public int $keysCreated;

    /**
     * @var int Number of existing translation keys reused
     */
    public int $keysReused;

    /**
     * @var array Array of error messages
     */
    public array $errors;

    /**
     * @var bool Whether the migration was successful
     */
    public bool $success;

    /**
     * @var string File path that was migrated
     */
    public string $filePath;

    /**
     * Create a new MigrationResult instance
     * 
     * @param string $filePath File path that was migrated
     * @param int $replacedCount Number of replacements made
     * @param int $keysCreated Number of keys created
     * @param int $keysReused Number of keys reused
     * @param array $errors Array of error messages
     * @param bool $success Whether migration was successful
     */
    public function __construct(
        string $filePath,
        int $replacedCount = 0,
        int $keysCreated = 0,
        int $keysReused = 0,
        array $errors = [],
        bool $success = true
    ) {
        $this->filePath = $filePath;
        $this->replacedCount = $replacedCount;
        $this->keysCreated = $keysCreated;
        $this->keysReused = $keysReused;
        $this->errors = $errors;
        $this->success = $success;
    }

    /**
     * Add an error message
     * 
     * @param string $error Error message
     * @return void
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
        $this->success = false;
    }

    /**
     * Get summary message
     * 
     * @return string Summary of migration result
     */
    public function getSummary(): string
    {
        if (!$this->success) {
            return "Migration failed for {$this->filePath}: " . implode(', ', $this->errors);
        }

        return "Successfully migrated {$this->filePath}: " .
               "{$this->replacedCount} replacements, " .
               "{$this->keysCreated} keys created, " .
               "{$this->keysReused} keys reused";
    }

    /**
     * Convert to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'file_path' => $this->filePath,
            'replaced_count' => $this->replacedCount,
            'keys_created' => $this->keysCreated,
            'keys_reused' => $this->keysReused,
            'errors' => $this->errors,
            'success' => $this->success,
        ];
    }
}
