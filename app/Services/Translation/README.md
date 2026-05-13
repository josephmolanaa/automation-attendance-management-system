# Translation Services

This directory contains services for managing and validating translations in the Attendance Management System.

## TranslationValidator

The `TranslationValidator` class validates translation files for completeness and consistency.

### Features

- **Key Parity Check**: Verifies that every translation key in one locale exists in all other locales
- **Naming Convention Validation**: Ensures all keys follow snake_case or kebab-case conventions
- **Empty Value Detection**: Identifies translation keys with empty or null values
- **Coverage Calculation**: Computes translation coverage percentage for each locale

### Usage

```php
use App\Services\Translation\TranslationValidator;

// Create validator instance
$validator = new TranslationValidator();

// Validate translation files for 'id' and 'en' locales
$report = $validator->validate(['id', 'en']);

// Check if validation passed
if ($report->isValid) {
    echo "All translations are valid!";
} else {
    echo "Some issues found:";
    echo $report->toMarkdown();
}

// Check key parity between two locales
$parityResult = $validator->checkKeyParity('id', 'en');
echo "Keys missing in 'id': " . count($parityResult['id']);
echo "Keys missing in 'en': " . count($parityResult['en']);
```

### Methods

#### `validate(array $locales = ['id', 'en']): ValidationReport`

Validates translation files for the specified locales.

**Parameters:**
- `$locales` - Array of locale codes to validate (default: `['id', 'en']`)

**Returns:** `ValidationReport` object containing validation results

#### `checkKeyParity(string $locale1, string $locale2): array`

Checks key parity between two locales.

**Parameters:**
- `$locale1` - First locale code
- `$locale2` - Second locale code

**Returns:** Array with missing keys: `[locale => [keys]]`

### ValidationReport

The `ValidationReport` class contains validation results and provides methods to generate reports.

#### Properties

- `missingKeys` - Array of missing keys per locale
- `invalidNaming` - Array of keys with invalid naming conventions
- `emptyValues` - Array of keys with empty values per locale
- `coverage` - Coverage percentage per locale
- `isValid` - Boolean indicating if validation passed
- `allowDeployment` - Always `true` per Requirement 13.7

#### Methods

##### `toMarkdown(): string`

Generates a human-readable markdown report of validation results.

**Example Output:**

```markdown
# Translation Validation Report

## Overall Status

- **Valid**: Yes
- **Deployment Allowed**: Yes

## Translation Coverage

- **id**: 100%
- **en**: 98.5%

## Missing Keys

### Missing in 'en' locale

- `nav.new_feature`
- `button.advanced_search`

## Summary

- Total missing keys: 2
- Total invalid naming: 0
- Total empty values: 0

⚠️ Some validation checks failed. Please review the issues above.

---

*Note: Deployment is allowed regardless of validation status (Requirement 13.7)*
```

### Requirements Satisfied

- **Requirement 4.1**: Verifies every translation key in one locale has a corresponding entry in other locales
- **Requirement 4.2**: Reports missing translations between locales
- **Requirement 4.3**: Validates naming conventions (snake_case/kebab-case)
- **Requirement 4.4**: Checks for empty string values
- **Requirement 4.5**: Generates completeness report with coverage percentage
- **Requirement 13.7**: Always allows deployment regardless of validation status

### Implementation Notes

- The validator currently only checks the `app.php` translation file
- Nested translation keys are flattened using dot notation (e.g., `nav.home`, `form.label.name`)
- Valid naming conventions allow lowercase letters, numbers, underscores, hyphens, and dots
- Empty values include both empty strings (`''`) and `null` values
- Coverage is calculated as: `(total keys - empty keys) / total keys * 100`

### Future Enhancements

To validate additional translation files (e.g., `validation.php`, `auth.php`), the `loadTranslations()` method can be extended to accept a file parameter.
