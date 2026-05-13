<?php

namespace App\Services\Translation;

class ValidationReport
{
    /**
     * Create a new ValidationReport instance
     * 
     * @param array $missingKeys Missing keys [locale => [keys]]
     * @param array $invalidNaming Invalid keys with reasons [key => reason]
     * @param array $emptyValues Keys with empty values [locale => [keys]]
     * @param array $coverage Coverage percentage [locale => percentage]
     * @param bool $isValid Whether validation passed
     * @param bool $allowDeployment Always true per Requirement 13.7
     */
    public function __construct(
        public array $missingKeys,
        public array $invalidNaming,
        public array $emptyValues,
        public array $coverage,
        public bool $isValid,
        public bool $allowDeployment = true
    ) {}

    /**
     * Generate markdown report
     * 
     * @return string Markdown formatted report
     */
    public function toMarkdown(): string
    {
        $markdown = "# Translation Validation Report\n\n";
        
        // Overall status
        $markdown .= "## Overall Status\n\n";
        $markdown .= "- **Valid**: " . ($this->isValid ? 'Yes' : 'No') . "\n";
        $markdown .= "- **Deployment Allowed**: " . ($this->allowDeployment ? 'Yes' : 'No') . "\n\n";
        
        // Coverage section
        $markdown .= "## Translation Coverage\n\n";
        foreach ($this->coverage as $locale => $percentage) {
            $markdown .= "- **{$locale}**: {$percentage}%\n";
        }
        $markdown .= "\n";
        
        // Missing keys section
        $markdown .= "## Missing Keys\n\n";
        $hasMissingKeys = false;
        foreach ($this->missingKeys as $locale => $keys) {
            if (!empty($keys)) {
                $hasMissingKeys = true;
                $markdown .= "### Missing in '{$locale}' locale\n\n";
                foreach ($keys as $key) {
                    $markdown .= "- `{$key}`\n";
                }
                $markdown .= "\n";
            }
        }
        if (!$hasMissingKeys) {
            $markdown .= "No missing keys found.\n\n";
        }
        
        // Invalid naming section
        $markdown .= "## Invalid Naming Conventions\n\n";
        if (empty($this->invalidNaming)) {
            $markdown .= "All keys follow valid naming conventions.\n\n";
        } else {
            foreach ($this->invalidNaming as $key => $reason) {
                $markdown .= "- `{$key}`: {$reason}\n";
            }
            $markdown .= "\n";
        }
        
        // Empty values section
        $markdown .= "## Empty Values\n\n";
        $hasEmptyValues = false;
        foreach ($this->emptyValues as $locale => $keys) {
            if (!empty($keys)) {
                $hasEmptyValues = true;
                $markdown .= "### Empty values in '{$locale}' locale\n\n";
                foreach ($keys as $key) {
                    $markdown .= "- `{$key}`\n";
                }
                $markdown .= "\n";
            }
        }
        if (!$hasEmptyValues) {
            $markdown .= "No empty values found.\n\n";
        }
        
        // Summary
        $markdown .= "## Summary\n\n";
        $totalMissing = array_sum(array_map('count', $this->missingKeys));
        $totalInvalid = count($this->invalidNaming);
        $totalEmpty = array_sum(array_map('count', $this->emptyValues));
        
        $markdown .= "- Total missing keys: {$totalMissing}\n";
        $markdown .= "- Total invalid naming: {$totalInvalid}\n";
        $markdown .= "- Total empty values: {$totalEmpty}\n\n";
        
        if ($this->isValid) {
            $markdown .= "✅ All validation checks passed!\n";
        } else {
            $markdown .= "⚠️ Some validation checks failed. Please review the issues above.\n";
        }
        
        $markdown .= "\n---\n\n";
        $markdown .= "*Note: Deployment is allowed regardless of validation status (Requirement 13.7)*\n";
        
        return $markdown;
    }
}
