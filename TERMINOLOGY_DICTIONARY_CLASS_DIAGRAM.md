# TerminologyDictionary Class Diagram

## Class Overview

```
┌─────────────────────────────────────────────────────────────┐
│                  TerminologyDictionary                      │
├─────────────────────────────────────────────────────────────┤
│ Properties (Protected):                                     │
│  - standardTerms: array<string, string>                     │
│  - deprecatedTerms: array<string, array<string>>            │
│  - englishTranslations: array<string, string>               │
│  - navigationTerms: array<string, string>                   │
│  - termToConcept: array<string, string>                     │
│  - loaded: bool                                             │
├─────────────────────────────────────────────────────────────┤
│ Public Methods:                                             │
│  + load(filePath: string): void                             │
│  + getStandardTerm(concept: string): string                 │
│  + getDeprecatedTerms(concept: string): array               │
│  + isNavigationTermUnique(concept: string, term: string)    │
│  + suggestEnglishTranslation(indonesianTerm: string)        │
│  + getAllStandardTerms(): array                             │
│  + getAllNavigationTerms(): array                           │
│  + hasConcept(concept: string): bool                        │
│  + isLoaded(): bool                                         │
├─────────────────────────────────────────────────────────────┤
│ Protected Methods:                                          │
│  # parseCoreBusinessTerms(content: string): void            │
│  # parseNavigationTerms(content: string): void              │
│  # parseFormAndActionTerms(content: string): void           │
│  # parseValidationTerms(content: string): void              │
│  # parseDataTablesTerms(content: string): void              │
│  # parseDateTimeTerms(content: string): void                │
│  # parseAlertTerms(content: string): void                   │
│  # parseTableSection(content, header, isNav): void          │
│  # parseTableRow(line: string, isNav: bool): void           │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow

```
┌──────────────────────┐
│ Markdown Dictionary  │
│ (terminology-        │
│  dictionary.md)      │
└──────────┬───────────┘
           │
           │ load()
           ▼
┌──────────────────────┐
│  Parse Sections:     │
│  - Core Business     │
│  - Navigation        │
│  - Forms & Actions   │
│  - Validation        │
│  - DataTables        │
│  - Date/Time         │
│  - Alerts            │
└──────────┬───────────┘
           │
           │ parseTableSection()
           ▼
┌──────────────────────┐
│  Parse Table Rows    │
│  Extract:            │
│  - Concept           │
│  - Standard Term     │
│  - Deprecated Terms  │
│  - English Trans.    │
└──────────┬───────────┘
           │
           │ parseTableRow()
           ▼
┌──────────────────────┐
│  Store in Arrays:    │
│  - standardTerms     │
│  - deprecatedTerms   │
│  - englishTrans.     │
│  - navigationTerms   │
│  - termToConcept     │
└──────────────────────┘
```

## Usage Examples

### Example 1: Load and Get Standard Term

```php
$dictionary = new TerminologyDictionary();
$dictionary->load(base_path('docs/terminology-dictionary.md'));

$term = $dictionary->getStandardTerm('employee');
// Returns: "Karyawan"
```

### Example 2: Get Deprecated Terms

```php
$deprecated = $dictionary->getDeprecatedTerms('employee');
// Returns: ["Pegawai", "Pekerja", "Staff"]
```

### Example 3: Suggest English Translation

```php
$english = $dictionary->suggestEnglishTranslation('Karyawan');
// Returns: "Employee"
```

### Example 4: Validate Navigation Term Uniqueness

```php
// Check if "Beranda" is unique for "home"
$isUnique = $dictionary->isNavigationTermUnique('home', 'Beranda');
// Returns: true

// Check if "Beranda" can be used for another concept
$isUnique = $dictionary->isNavigationTermUnique('main', 'Beranda');
// Returns: false (already used by "home")
```

### Example 5: Get All Terms

```php
$allTerms = $dictionary->getAllStandardTerms();
// Returns: [
//   'employee' => 'Karyawan',
//   'attendance' => 'Absensi',
//   'schedule' => 'Jadwal',
//   ...
// ]

$navTerms = $dictionary->getAllNavigationTerms();
// Returns: [
//   'home' => 'Beranda',
//   'dashboard' => 'Dashboard',
//   'employees' => 'Karyawan',
//   ...
// ]
```

## Internal Data Structures

### standardTerms Array
```php
[
    'employee' => 'Karyawan',
    'attendance' => 'Absensi',
    'schedule' => 'Jadwal',
    'overtime' => 'Lembur',
    ...
]
```

### deprecatedTerms Array
```php
[
    'employee' => ['Pegawai', 'Pekerja', 'Staff'],
    'attendance' => ['Kehadiran', 'Presensi'],
    'schedule' => ['Schedule'],
    'overtime' => ['Overtime', 'Kerja Lembur'],
    ...
]
```

### englishTranslations Array
```php
[
    'employee' => 'Employee',
    'attendance' => 'Attendance',
    'schedule' => 'Schedule',
    'overtime' => 'Overtime',
    ...
]
```

### navigationTerms Array
```php
[
    'home' => 'Beranda',
    'dashboard' => 'Dashboard',
    'employees' => 'Karyawan',
    'attendance' => 'Absensi',
    ...
]
```

### termToConcept Array (Reverse Mapping)
```php
[
    'Karyawan' => 'employee',
    'Absensi' => 'attendance',
    'Jadwal' => 'schedule',
    'Lembur' => 'overtime',
    ...
]
```

## Error Handling

The class throws exceptions in the following cases:

1. **File Not Found**
   ```php
   throw new Exception("Dictionary file not found: {$filePath}");
   ```

2. **File Read Failure**
   ```php
   throw new Exception("Failed to read dictionary file: {$filePath}");
   ```

3. **Dictionary Not Loaded**
   ```php
   throw new Exception("Dictionary not loaded. Call load() first.");
   ```

4. **Concept Not Found**
   ```php
   throw new Exception("Concept not found in dictionary: {$concept}");
   ```

## Integration Points

The TerminologyDictionary class integrates with:

1. **TextMigrator** (Task 5.1)
   - Uses `getStandardTerm()` to ensure consistent terminology
   - Uses `getDeprecatedTerms()` to identify terms needing replacement

2. **HardcodedTextAuditor** (Task 2.1)
   - Uses `getAllStandardTerms()` to validate terminology usage
   - Uses `getDeprecatedTerms()` to flag deprecated terms

3. **TranslationValidator** (Task 6.1)
   - Uses `isNavigationTermUnique()` to validate navigation terms
   - Uses `suggestEnglishTranslation()` for missing translations

4. **Language Switcher** (Task 8.1)
   - Uses `getAllNavigationTerms()` for menu translations
   - Uses `suggestEnglishTranslation()` for English locale

## Requirements Satisfied

✓ **Requirement 2.1**: Define exactly one standard Indonesian term per concept
✓ **Requirement 2.2**: Include specific mappings (Karyawan, Absensi, Jadwal, Lembur)
✓ **Requirement 2.3**: Specify which terms to use and which to replace
✓ **Requirement 2.4**: Document in markdown file accessible to developers
✓ **Requirement 2.5**: Include at least 50 core business terms
✓ **Requirement 9.4**: Enforce unique navigation terms

## Performance Considerations

- **Memory**: All terms loaded into memory for fast access
- **Parsing**: One-time cost during `load()` call
- **Lookups**: O(1) time complexity using associative arrays
- **Caching**: Consider caching loaded dictionary in production

## Best Practices

1. **Load Once**: Load dictionary once at application startup
2. **Singleton Pattern**: Consider using singleton for shared instance
3. **Service Provider**: Register in Laravel service provider
4. **Dependency Injection**: Inject into classes that need it
5. **Error Handling**: Always wrap in try-catch blocks

## Example Service Provider Registration

```php
// app/Providers/TranslationServiceProvider.php
public function register()
{
    $this->app->singleton(TerminologyDictionary::class, function ($app) {
        $dictionary = new TerminologyDictionary();
        $dictionary->load(base_path('docs/terminology-dictionary.md'));
        return $dictionary;
    });
}
```

## Example Usage in Controller

```php
use App\Services\Translation\TerminologyDictionary;

class EmployeeController extends Controller
{
    protected $dictionary;
    
    public function __construct(TerminologyDictionary $dictionary)
    {
        $this->dictionary = $dictionary;
    }
    
    public function index()
    {
        // Use standard term in view
        $employeeTerm = $this->dictionary->getStandardTerm('employee');
        
        return view('employees.index', [
            'employeeTerm' => $employeeTerm
        ]);
    }
}
```
