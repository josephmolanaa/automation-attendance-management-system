<?php

namespace App\Services\Translation;

use Exception;

/**
 * Terminology Dictionary
 * 
 * Manages standardized terminology mappings for Indonesian language standardization.
 * Loads and parses the terminology dictionary markdown file to provide:
 * - Standard Indonesian terms for business concepts
 * - Deprecated terms that need replacement
 * - English translation suggestions
 * - Navigation term uniqueness validation
 * 
 * Requirements: 2.1, 2.2, 2.3
 */
class TerminologyDictionary
{
    /**
     * Standard terms mapping: concept => standard Indonesian term
     * 
     * @var array<string, string>
     */
    protected array $standardTerms = [];

    /**
     * Deprecated terms mapping: concept => array of deprecated terms
     * 
     * @var array<string, array<string>>
     */
    protected array $deprecatedTerms = [];

    /**
     * English translations mapping: concept => English translation
     * 
     * @var array<string, string>
     */
    protected array $englishTranslations = [];

    /**
     * Navigation terms mapping: concept => standard Indonesian term
     * Used for uniqueness validation
     * 
     * @var array<string, string>
     */
    protected array $navigationTerms = [];

    /**
     * Reverse mapping: Indonesian term => concept
     * Used for quick lookups
     * 
     * @var array<string, string>
     */
    protected array $termToConcept = [];

    /**
     * Whether the dictionary has been loaded
     * 
     * @var bool
     */
    protected bool $loaded = false;

    /**
     * Load dictionary from markdown file
     * 
     * Parses the terminology dictionary markdown file and extracts:
     * - Core business terms
     * - Navigation terms
     * - Form and action terms
     * - Validation and error messages
     * - DataTables UI terms
     * - Date and time terms
     * - Alert and confirmation terms
     * 
     * @param string $filePath Path to dictionary file
     * @return void
     * @throws Exception If file cannot be read or parsed
     */
    public function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new Exception("Dictionary file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Failed to read dictionary file: {$filePath}");
        }

        // Parse different sections of the dictionary
        $this->parseCoreBusinessTerms($content);
        $this->parseNavigationTerms($content);
        $this->parseFormAndActionTerms($content);
        $this->parseValidationTerms($content);
        $this->parseDataTablesTerms($content);
        $this->parseDateTimeTerms($content);
        $this->parseAlertTerms($content);

        $this->loaded = true;
    }

    /**
     * Parse Core Business Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseCoreBusinessTerms(string $content): void
    {
        $this->parseTableSection($content, '## Core Business Terms', false);
    }

    /**
     * Parse Navigation Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseNavigationTerms(string $content): void
    {
        $this->parseTableSection($content, '## Navigation Terms (Must be Unique)', true);
    }

    /**
     * Parse Form and Action Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseFormAndActionTerms(string $content): void
    {
        $this->parseTableSection($content, '## Form and Action Terms', false);
    }

    /**
     * Parse Validation and Error Messages section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseValidationTerms(string $content): void
    {
        $this->parseTableSection($content, '## Validation and Error Messages', false);
    }

    /**
     * Parse DataTables UI Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseDataTablesTerms(string $content): void
    {
        $this->parseTableSection($content, '## DataTables UI Terms', true);
    }

    /**
     * Parse Date and Time Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseDateTimeTerms(string $content): void
    {
        $this->parseTableSection($content, '## Date and Time Terms', true);
    }

    /**
     * Parse Alert and Confirmation Terms section
     * 
     * @param string $content Markdown content
     * @return void
     */
    protected function parseAlertTerms(string $content): void
    {
        $this->parseTableSection($content, '## Alert and Confirmation Terms', false);
    }

    /**
     * Parse a table section from markdown content
     * 
     * @param string $content Markdown content
     * @param string $sectionHeader Section header to find
     * @param bool $isNavigationSection Whether this is a navigation section
     * @return void
     */
    protected function parseTableSection(string $content, string $sectionHeader, bool $isNavigationSection): void
    {
        // Find the section
        $sectionPos = strpos($content, $sectionHeader);
        if ($sectionPos === false) {
            return;
        }

        // Extract content from this section until next ## header
        $nextSectionPos = strpos($content, "\n## ", $sectionPos + strlen($sectionHeader));
        $sectionContent = $nextSectionPos !== false
            ? substr($content, $sectionPos, $nextSectionPos - $sectionPos)
            : substr($content, $sectionPos);

        // Find the table (starts with |)
        $lines = explode("\n", $sectionContent);
        $inTable = false;
        $headerParsed = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Check if this is a table row
            if (strpos($line, '|') === 0) {
                // Skip header separator line (|---|---|)
                if (strpos($line, '---') !== false) {
                    $inTable = true;
                    $headerParsed = true;
                    continue;
                }

                // Skip header row
                if (!$headerParsed) {
                    continue;
                }

                // Parse data row
                $this->parseTableRow($line, $isNavigationSection);
            }
        }
    }

    /**
     * Parse a single table row
     * 
     * @param string $line Table row line
     * @param bool $isNavigationSection Whether this is from navigation section
     * @return void
     */
    protected function parseTableRow(string $line, bool $isNavigationSection): void
    {
        // Split by | and trim
        $columns = array_map('trim', explode('|', $line));
        
        // Remove first and last empty elements (from leading/trailing |)
        $columns = array_filter($columns, function($col) {
            return !empty($col);
        });
        $columns = array_values($columns);

        // Need at least 3 columns: concept, standard term, english
        if (count($columns) < 3) {
            return;
        }

        $concept = $columns[0];
        $standardTerm = $columns[1];
        $englishTranslation = '';
        $deprecatedTermsList = [];

        if ($isNavigationSection) {
            // Navigation section format: Concept | Standard Term (ID) | English Translation | Usage Context
            $englishTranslation = $columns[2];
            $this->navigationTerms[$concept] = $standardTerm;
        } else {
            // Regular section format: Concept | Standard Term (ID) | Deprecated Terms | English Translation | Notes
            if (count($columns) >= 4) {
                $deprecatedTermsStr = $columns[2];
                $englishTranslation = $columns[3];

                // Parse deprecated terms (comma-separated)
                if (!empty($deprecatedTermsStr) && $deprecatedTermsStr !== '-') {
                    $deprecatedTermsList = array_map('trim', explode(',', $deprecatedTermsStr));
                }
            } elseif (count($columns) >= 3) {
                // Some sections might not have deprecated terms column
                $englishTranslation = $columns[2];
            }
        }

        // Store mappings
        $this->standardTerms[$concept] = $standardTerm;
        $this->englishTranslations[$concept] = $englishTranslation;
        $this->deprecatedTerms[$concept] = $deprecatedTermsList;
        $this->termToConcept[$standardTerm] = $concept;
    }

    /**
     * Get standard term for a concept
     * 
     * @param string $concept Business concept identifier
     * @return string Standard Indonesian term
     * @throws Exception If concept not found
     */
    public function getStandardTerm(string $concept): string
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        if (!isset($this->standardTerms[$concept])) {
            throw new Exception("Concept not found in dictionary: {$concept}");
        }

        return $this->standardTerms[$concept];
    }

    /**
     * Get deprecated terms for a concept
     * 
     * @param string $concept Business concept identifier
     * @return array List of deprecated terms to replace
     */
    public function getDeprecatedTerms(string $concept): array
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        return $this->deprecatedTerms[$concept] ?? [];
    }

    /**
     * Validate term uniqueness for navigation concepts
     * 
     * Checks if the proposed term is unique among navigation terms.
     * Navigation terms must be distinct to avoid user confusion.
     * 
     * @param string $concept Navigation concept
     * @param string $term Proposed term
     * @return bool True if term is unique
     */
    public function isNavigationTermUnique(string $concept, string $term): bool
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        // Check if this term is already used by a different navigation concept
        foreach ($this->navigationTerms as $existingConcept => $existingTerm) {
            if ($existingConcept !== $concept && $existingTerm === $term) {
                return false;
            }
        }

        return true;
    }

    /**
     * Suggest English translation for Indonesian term
     * 
     * Looks up the Indonesian term in the dictionary and returns
     * the corresponding English translation.
     * 
     * @param string $indonesianTerm Indonesian term
     * @return string Suggested English translation
     */
    public function suggestEnglishTranslation(string $indonesianTerm): string
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        // Find concept for this Indonesian term
        $concept = $this->termToConcept[$indonesianTerm] ?? null;

        if ($concept && isset($this->englishTranslations[$concept])) {
            return $this->englishTranslations[$concept];
        }

        // Fallback: return the term itself if not found
        return $indonesianTerm;
    }

    /**
     * Get all standard terms
     * 
     * @return array<string, string> Concept => standard term mapping
     */
    public function getAllStandardTerms(): array
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        return $this->standardTerms;
    }

    /**
     * Get all navigation terms
     * 
     * @return array<string, string> Concept => standard term mapping
     */
    public function getAllNavigationTerms(): array
    {
        if (!$this->loaded) {
            throw new Exception("Dictionary not loaded. Call load() first.");
        }

        return $this->navigationTerms;
    }

    /**
     * Check if a concept exists in the dictionary
     * 
     * @param string $concept Business concept identifier
     * @return bool True if concept exists
     */
    public function hasConcept(string $concept): bool
    {
        return isset($this->standardTerms[$concept]);
    }

    /**
     * Check if dictionary is loaded
     * 
     * @return bool True if loaded
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }
}
