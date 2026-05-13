<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of locales supported by the application.
    | Currently supports Indonesian (id) and English (en).
    |
    */
    'locales' => ['id', 'en'],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale for the application.
    | Set to Indonesian ('id') per Requirement 10.5.
    |
    */
    'default_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale to use when a translation key is not found
    | in the current locale.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Storage Method
    |--------------------------------------------------------------------------
    |
    | Method for storing user locale preference.
    | Options: 'session', 'cookie', or 'both'
    |
    */
    'storage_method' => 'both',

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for locale preference cookie storage.
    |
    */
    'cookie_name' => 'app_locale',
    'cookie_lifetime' => 525600, // 1 year in minutes

    /*
    |--------------------------------------------------------------------------
    | Block on Storage Failure
    |--------------------------------------------------------------------------
    |
    | If true, language switch will be blocked entirely when storage fails
    | (disabled cookies or session issues).
    | Set to true per Requirement 10.3.
    |
    */
    'block_on_storage_failure' => true,

    /*
    |--------------------------------------------------------------------------
    | Audit Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the hardcoded text audit tool.
    |
    */
    'audit' => [
        // Paths to scan for hardcoded text
        'scan_paths' => ['resources/views'],

        // Patterns to exclude from scanning
        'exclude_patterns' => ['*.blade.php.bak', '*/vendor/*', '*/node_modules/*'],

        // Priority categorization based on file paths
        'priority_paths' => [
            'high' => ['layouts', 'dashboard', 'partials/sidebar', 'partials/header', 'partials/nav'],
            'medium' => ['forms', 'modals', 'components'],
            'low' => ['errors', 'emails', 'vendor'],
        ],

        // Output path for audit reports
        'report_path' => 'storage/logs/translation-audit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for migrating hardcoded text to translation system.
    |
    */
    'migration' => [
        // Enable backup of files before migration
        'backup_enabled' => true,

        // Path to store backup files
        'backup_path' => 'storage/backups/translations',

        // Number of files to process in a single batch
        'batch_size' => 50,

        // Treat missing keys as non-existent and create new entries
        // with duplicate detection (Requirement 3.6)
        'treat_missing_as_nonexistent' => true,

        // Automatically detect and reuse duplicate translations
        'enable_duplicate_detection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for translation file validation.
    |
    */
    'validation' => [
        // Naming convention for translation keys
        // Options: 'snake_case', 'kebab-case'
        'naming_convention' => 'snake_case',

        // Allow empty string values in translation files
        'allow_empty_values' => false,

        // Require all keys to exist in all locales
        'require_key_parity' => true,

        // Allow deployment regardless of translation coverage percentage
        // Set to true per Requirement 13.7
        'allow_deployment_regardless_coverage' => true,

        // Minimum coverage percentage for warnings (not blocking)
        'coverage_warning_threshold' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for translation testing and validation.
    |
    */
    'testing' => [
        // Automatically attempt to correct display language mismatches
        // (Requirement 13.4)
        'auto_correct_display' => true,

        // Allow untranslated messages for internal system errors
        // that users should not normally see (Requirement 14.5)
        'allow_untranslated_internal_errors' => true,

        // Test report output path
        'report_path' => 'storage/logs/translation-tests',
    ],

    /*
    |--------------------------------------------------------------------------
    | Terminology Dictionary
    |--------------------------------------------------------------------------
    |
    | Path to the terminology dictionary file that defines
    | standard terms and their translations.
    |
    */
    'terminology_dictionary_path' => 'docs/terminology-dictionary.md',

    /*
    |--------------------------------------------------------------------------
    | Translation File Paths
    |--------------------------------------------------------------------------
    |
    | Paths to translation files for each locale.
    |
    */
    'translation_paths' => [
        'id' => 'resources/lang/id',
        'en' => 'resources/lang/en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Prefixes
    |--------------------------------------------------------------------------
    |
    | Standard prefixes for organizing translation keys by category.
    |
    */
    'key_prefixes' => [
        'navigation' => 'nav',
        'form' => 'form',
        'button' => 'button',
        'message' => 'message',
        'alert' => 'alert',
        'datatable' => 'datatable',
        'validation' => 'validation',
        'page_title' => 'page_title',
        'date' => 'date',
        'month' => 'month',
        'day' => 'day',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging for translation operations.
    |
    */
    'logging' => [
        'enabled' => true,
        'channel' => 'daily',
        'level' => 'info',
    ],
];
