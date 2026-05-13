<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TextMigrator
{
    /**
     * @var TerminologyDictionary
     */
    private TerminologyDictionary $dictionary;

    /**
     * @var array Cached translation files
     */
    private array $translationCache = [];

    /**
     * Create a new TextMigrator instance
     * 
     * @param TerminologyDictionary|null $dictionary
     */
    public function __construct(?TerminologyDictionary $dictionary = null)
    {
        $this->dictionary = $dictionary ?? new TerminologyDictionary();
        $this->loadTranslationCache();
    }

    /**
     * Load translation files into cache
     * 
     * @return void
     */
    private function loadTranslationCache(): void
    {
        $locales = ['id', 'en'];
        
        foreach ($locales as $locale) {
            $filePath = resource_path("lang/{$locale}/app.php");
            
            if (File::exists($filePath)) {
                $this->translationCache[$locale] = include $filePath;
            } else {
                $this->translationCache[$locale] = [];
            }
        }
    }

    /**
     * Migrate hardcoded text in a blade file
     * 
     * @param string $filePath Path to blade file
     * @param array $findings Audit findings for this file
     * @param TerminologyDictionary|null $dictionary Optional dictionary override
     * @return MigrationResult
     */
    public function migrateFile(
        string $filePath,
        array $findings,
        ?TerminologyDictionary $dictionary = null
    ): MigrationResult {
        if ($dictionary !== null) {
            $this->dictionary = $dictionary;
        }

        $result = new MigrationResult($filePath);

        // Check if file exists
        if (!File::exists($filePath)) {
            $result->addError("File not found: {$filePath}");
            return $result;
        }

        // Read file content
        $content = File::get($filePath);
        $originalContent = $content;

        // Create backup
        if (config('translation.migration.backup_enabled', true)) {
            $this->createBackup($filePath, $content);
        }

        // Process each finding
        foreach ($findings as $finding) {
            $text = $finding['text'];
            $context = $finding['context'] ?? '';

            // Generate or find translation key
            $key = $this->generateTranslationKey($text, $context);

            // Check if key exists or find duplicate
            if ($this->keyExists($key)) {
                // Key exists, reuse it
                $content = $this->replaceWithKey($content, $text, $key);
                $result->replacedCount++;
                $result->keysReused++;
                Log::info("Reusing existing translation key: {$key}");
            } else {
                // Check for duplicate text
                $existingKey = $this->findDuplicateKey($text);
                
                if ($existingKey) {
                    // Found duplicate, use existing key
                    $content = $this->replaceWithKey($content, $text, $existingKey);
                    $result->replacedCount++;
                    $result->keysReused++;
                    Log::warning("Duplicate translation found: '{$text}' exists as '{$existingKey}'");
                } else {
                    // Create new translation entry
                    $englishText = $this->getEnglishTranslation($text);
                    
                    try {
                        $this->addTranslationEntry($key, $text, $englishText);
                        $content = $this->replaceWithKey($content, $text, $key);
                        $result->replacedCount++;
                        $result->keysCreated++;
                        Log::info("Created new translation key: {$key}");
                    } catch (\Exception $e) {
                        $result->addError("Failed to create translation key '{$key}': {$e->getMessage()}");
                        Log::error("Failed to create translation key '{$key}': {$e->getMessage()}");
                    }
                }
            }
        }

        // Write modified content back to file if changes were made
        if ($content !== $originalContent && $result->replacedCount > 0) {
            try {
                File::put($filePath, $content);
            } catch (\Exception $e) {
                $result->addError("Failed to write file: {$e->getMessage()}");
                Log::error("Failed to write file '{$filePath}': {$e->getMessage()}");
            }
        }

        return $result;
    }

    /**
     * Replace text with translation key reference
     * Preserves HTML attributes, CSS classes, and JavaScript functionality
     * 
     * @param string $content File content
     * @param string $text Text to replace
     * @param string $key Translation key
     * @return string Modified content
     */
    public function replaceWithKey(string $content, string $text, string $key): string
    {
        // Escape special regex characters in the text
        $escapedText = preg_quote($text, '/');

        // Pattern 1: Text within double quotes (e.g., "Hardcoded Text")
        // Replace with {{ __('key') }}
        $pattern1 = '/"(' . $escapedText . ')"/u';
        $replacement1 = '{{ __(\'' . $key . '\') }}';
        $content = preg_replace($pattern1, $replacement1, $content, 1);

        // Pattern 2: Text within single quotes (e.g., 'Hardcoded Text')
        // Replace with {{ __('key') }}
        $pattern2 = "/(')" . $escapedText . "(')/u";
        $replacement2 = '{{ __(\'' . $key . '\') }}';
        $content = preg_replace($pattern2, $replacement2, $content, 1);

        // Pattern 3: Text within HTML tags (e.g., <span>Hardcoded Text</span>)
        // Replace with {{ __('key') }}
        $pattern3 = '/>(\s*)' . $escapedText . '(\s*)</u';
        $replacement3 = '>$1{{ __(\'' . $key . '\') }}$2<';
        $content = preg_replace($pattern3, $replacement3, $content, 1);

        // Pattern 4: Text in HTML attributes (e.g., placeholder="Hardcoded Text")
        // Replace with {{ __('key') }}
        $pattern4 = '/(\w+\s*=\s*")' . $escapedText . '(")/u';
        $replacement4 = '$1{{ __(\'' . $key . '\') }}$2';
        $content = preg_replace($pattern4, $replacement4, $content, 1);

        return $content;
    }

    /**
     * Add translation entry to files
     * Treats missing keys as non-existent and creates new entries with duplicate detection
     * 
     * @param string $key Translation key
     * @param string $indonesianText Indonesian translation
     * @param string $englishText English translation
     * @return void
     * @throws \Exception If unable to write translation files
     */
    public function addTranslationEntry(
        string $key,
        string $indonesianText,
        string $englishText
    ): void {
        // Add to cache
        $this->translationCache['id'][$key] = $indonesianText;
        $this->translationCache['en'][$key] = $englishText;

        // Write to files
        $this->writeTranslationFile('id', $this->translationCache['id']);
        $this->writeTranslationFile('en', $this->translationCache['en']);
    }

    /**
     * Write translation array to file
     * 
     * @param string $locale Locale code (id, en)
     * @param array $translations Translation array
     * @return void
     * @throws \Exception If unable to write file
     */
    private function writeTranslationFile(string $locale, array $translations): void
    {
        $filePath = resource_path("lang/{$locale}/app.php");
        
        // Sort translations by key for better readability
        ksort($translations);

        // Generate PHP array code
        $content = "<?php\n\nreturn [\n";
        
        foreach ($translations as $key => $value) {
            // Escape single quotes in value
            $escapedValue = str_replace("'", "\\'", $value);
            $content .= "    '{$key}' => '{$escapedValue}',\n";
        }
        
        $content .= "];\n";

        // Ensure directory exists
        $directory = dirname($filePath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Write file
        if (File::put($filePath, $content) === false) {
            throw new \Exception("Failed to write translation file: {$filePath}");
        }
    }

    /**
     * Check if translation key already exists
     * 
     * @param string $key Translation key
     * @return bool
     */
    public function keyExists(string $key): bool
    {
        return isset($this->translationCache['id'][$key]) || 
               isset($this->translationCache['en'][$key]);
    }

    /**
     * Find existing key with same Indonesian text (duplicate detection)
     * 
     * @param string $text Indonesian text
     * @return string|null Existing key or null
     */
    public function findDuplicateKey(string $text): ?string
    {
        // Search in Indonesian translations
        foreach ($this->translationCache['id'] as $key => $value) {
            if (trim($value) === trim($text)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Generate translation key from text and context
     * 
     * @param string $text Text to generate key for
     * @param string $context Context (file path or code)
     * @return string Generated translation key
     */
    private function generateTranslationKey(string $text, string $context): string
    {
        // Use HardcodedTextAuditor's logic for consistency
        $auditor = new HardcodedTextAuditor();
        return $auditor->suggestKeyName($text, $context);
    }

    /**
     * Get English translation for Indonesian text
     * Uses dictionary if loaded, otherwise provides fallback
     * 
     * @param string $indonesianText Indonesian text
     * @return string English translation
     */
    private function getEnglishTranslation(string $indonesianText): string
    {
        try {
            if ($this->dictionary->isLoaded()) {
                return $this->dictionary->suggestEnglishTranslation($indonesianText);
            }
        } catch (\Exception $e) {
            Log::warning("Dictionary lookup failed: {$e->getMessage()}");
        }

        // Fallback: return the Indonesian text itself
        // This allows migration to proceed even without a loaded dictionary
        return $indonesianText;
    }

    /**
     * Create backup of file before migration
     * 
     * @param string $filePath Original file path
     * @param string $content File content
     * @return void
     */
    private function createBackup(string $filePath, string $content): void
    {
        $backupPath = config('translation.migration.backup_path', 'storage/backups/translations');
        $backupDir = base_path($backupPath);

        // Ensure backup directory exists
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Generate backup filename with timestamp
        $filename = basename($filePath);
        $timestamp = date('Y-m-d_His');
        $backupFile = $backupDir . '/' . $filename . '.' . $timestamp . '.bak';

        // Write backup
        File::put($backupFile, $content);
        Log::info("Created backup: {$backupFile}");
    }

    /**
     * Get translation cache
     * 
     * @return array Translation cache
     */
    public function getTranslationCache(): array
    {
        return $this->translationCache;
    }

    /**
     * Reload translation cache from files
     * 
     * @return void
     */
    public function reloadTranslationCache(): void
    {
        $this->translationCache = [];
        $this->loadTranslationCache();
    }
}
