<?php

namespace App\Services\Translation;

/**
 * Class SwitchResult
 * 
 * Represents the result of a language switch operation.
 * Includes success status, locale, error message, and blocked flag.
 * 
 * @package App\Services\Translation
 */
class SwitchResult
{
    /**
     * @var bool Whether the language switch was successful
     */
    public bool $success;

    /**
     * @var string The locale that was set (or attempted to be set)
     */
    public string $locale;

    /**
     * @var string|null Error message if the switch failed
     */
    public ?string $error;

    /**
     * @var bool Whether the switch was blocked due to storage failure
     */
    public bool $blocked;

    /**
     * Create a new SwitchResult instance
     *
     * @param bool $success
     * @param string $locale
     * @param string|null $error
     * @param bool $blocked
     */
    public function __construct(
        bool $success,
        string $locale,
        ?string $error = null,
        bool $blocked = false
    ) {
        $this->success = $success;
        $this->locale = $locale;
        $this->error = $error;
        $this->blocked = $blocked;
    }

    /**
     * Check if the switch was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if the switch was blocked
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    /**
     * Get the error message
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Convert to array representation
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'locale' => $this->locale,
            'error' => $this->error,
            'blocked' => $this->blocked,
        ];
    }
}
