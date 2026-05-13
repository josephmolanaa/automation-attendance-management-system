<?php

namespace App\Services\Translation;

class TranslationValidator
{
    /**
     * Validate translation files
     * 
     * @param array $locales Locales to validate (default: ['id', 'en'])
     * @return ValidationReport
     */
    public function validate(array $locales = ['id', 'en']): ValidationReport
    {
        $missingKeys = [];
        $invalidNaming = [];
        $emptyValues = [];
        $coverage = [];

        // Check key parity between locales
        if (count($locales) >= 2) {
            $missingKeys = $this->checkKeyParity($locales[0], $locales[1]);
        }

        // Validate naming conventions for all locales
        foreach ($locales as $locale) {
            $keys = $this->getAllKeys($locale);
            $invalidNaming = array_merge($invalidNaming, $this->validateNamingConventions($keys));
        }

        // Check for empty values in each locale
        foreach ($locales as $locale) {
            $emptyValues[$locale] = $this->checkEmptyValues($locale);
        }

        // Calculate coverage for each locale
        foreach ($locales as $locale) {
            $coverage[$locale] = $this->calculateCoverage($locale);
        }

        $isValid = empty($missingKeys['id']) && empty($missingKeys['en']) 
                   && empty($invalidNaming) 
                   && empty(array_filter($emptyValues));

        return new ValidationReport(
            missingKeys: $missingKeys,
            invalidNaming: $invalidNaming,
            emptyValues: $emptyValues,
            coverage: $coverage,
            isValid: $isValid,
            allowDeployment: true // Always true per Requirement 13.7
        );
    }

    /**
     * Check key parity between locales
     * 
     * @param string $locale1 First locale
     * @param string $locale2 Second locale
     * @return array Missing keys [locale => [keys]]
     */
    public function checkKeyParity(string $locale1, string $locale2): array
    {
        $keys1 = $this->getAllKeys($locale1);
        $keys2 = $this->getAllKeys($locale2);

        $missingInLocale2 = array_diff($keys1, $keys2);
        $missingInLocale1 = array_diff($keys2, $keys1);

        return [
            $locale1 => array_values($missingInLocale1),
            $locale2 => array_values($missingInLocale2),
        ];
    }

    /**
     * Validate naming conventions
     * 
     * @param array $keys Translation keys
     * @return array Invalid keys with reasons
     */
    public function validateNamingConventions(array $keys): array
    {
        $invalid = [];

        foreach ($keys as $key) {
            // Check if key follows snake_case or kebab-case
            if (!$this->isValidNamingConvention($key)) {
                $invalid[$key] = 'Key does not follow snake_case or kebab-case convention';
            }
        }

        return $invalid;
    }

    /**
     * Check for empty values
     * 
     * @param string $locale Locale to check
     * @return array Keys with empty values
     */
    public function checkEmptyValues(string $locale): array
    {
        $translations = $this->loadTranslations($locale);
        $emptyKeys = [];

        $this->findEmptyValues($translations, '', $emptyKeys);

        return $emptyKeys;
    }

    /**
     * Calculate coverage percentage
     * 
     * @param string $locale Locale to check
     * @return float Coverage percentage (0-100)
     */
    public function calculateCoverage(string $locale): float
    {
        $translations = $this->loadTranslations($locale);
        $totalKeys = count($this->getAllKeys($locale));
        $emptyKeys = count($this->checkEmptyValues($locale));

        if ($totalKeys === 0) {
            return 0.0;
        }

        $translatedKeys = $totalKeys - $emptyKeys;
        return round(($translatedKeys / $totalKeys) * 100, 2);
    }

    /**
     * Get all translation keys for a locale
     * 
     * @param string $locale Locale code
     * @return array Flat array of all keys
     */
    private function getAllKeys(string $locale): array
    {
        $translations = $this->loadTranslations($locale);
        $keys = [];

        $this->flattenKeys($translations, '', $keys);

        return $keys;
    }

    /**
     * Load translations for a locale
     * 
     * @param string $locale Locale code
     * @return array Translation array
     */
    private function loadTranslations(string $locale): array
    {
        $filePath = resource_path("lang/{$locale}/app.php");

        if (!file_exists($filePath)) {
            return [];
        }

        return require $filePath;
    }

    /**
     * Flatten nested translation keys
     * 
     * @param array $array Translation array
     * @param string $prefix Current key prefix
     * @param array &$result Result array
     * @return void
     */
    private function flattenKeys(array $array, string $prefix, array &$result): void
    {
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $this->flattenKeys($value, $fullKey, $result);
            } else {
                $result[] = $fullKey;
            }
        }
    }

    /**
     * Find empty values in translations
     * 
     * @param array $array Translation array
     * @param string $prefix Current key prefix
     * @param array &$result Result array
     * @return void
     */
    private function findEmptyValues(array $array, string $prefix, array &$result): void
    {
        foreach ($array as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $this->findEmptyValues($value, $fullKey, $result);
            } elseif ($value === '' || $value === null) {
                $result[] = $fullKey;
            }
        }
    }

    /**
     * Check if key follows valid naming convention
     * 
     * @param string $key Translation key
     * @return bool
     */
    private function isValidNamingConvention(string $key): bool
    {
        // Allow snake_case, kebab-case, and dot notation for nested keys
        // Valid patterns: lowercase letters, numbers, underscores, hyphens, and dots
        return preg_match('/^[a-z0-9_\-\.]+$/', $key) === 1;
    }
}
