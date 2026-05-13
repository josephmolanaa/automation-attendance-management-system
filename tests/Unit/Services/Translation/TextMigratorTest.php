<?php

namespace Tests\Unit\Services\Translation;

use Tests\TestCase;
use App\Services\Translation\TextMigrator;
use App\Services\Translation\MigrationResult;
use App\Services\Translation\TerminologyDictionary;
use Illuminate\Support\Facades\File;

class TextMigratorTest extends TestCase
{
    private TextMigrator $migrator;
    private string $testViewsPath;
    private string $testLangPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrator = new TextMigrator();
        $this->testViewsPath = base_path('tests/fixtures/views');
        $this->testLangPath = base_path('tests/fixtures/lang');
        
        // Create test directories
        File::makeDirectory($this->testViewsPath, 0755, true, true);
        File::makeDirectory($this->testLangPath . '/id', 0755, true, true);
        File::makeDirectory($this->testLangPath . '/en', 0755, true, true);
    }

    protected function tearDown(): void
    {
        // Clean up test fixtures
        if (File::exists(base_path('tests/fixtures'))) {
            File::deleteDirectory(base_path('tests/fixtures'));
        }
        parent::tearDown();
    }

    /** @test */
    public function it_can_replace_hardcoded_text_with_translation_key()
    {
        $content = '<h1>Karyawan</h1>';
        $text = 'Karyawan';
        $key = 'app.employee';

        $result = $this->migrator->replaceWithKey($content, $text, $key);

        $this->assertStringContainsString("{{ __('app.employee') }}", $result);
        $this->assertStringNotContainsString('<h1>Karyawan</h1>', $result);
    }

    /** @test */
    public function it_preserves_html_attributes_during_replacement()
    {
        $content = '<input type="text" placeholder="Nama Karyawan" class="form-control">';
        $text = 'Nama Karyawan';
        $key = 'form.label.employee_name';

        $result = $this->migrator->replaceWithKey($content, $text, $key);

        $this->assertStringContainsString('type="text"', $result);
        $this->assertStringContainsString('class="form-control"', $result);
        $this->assertStringContainsString("{{ __('form.label.employee_name') }}", $result);
    }

    /** @test */
    public function it_can_check_if_translation_key_exists()
    {
        // Key should not exist initially
        $this->assertFalse($this->migrator->keyExists('test.new_key'));

        // Add a translation entry
        $this->migrator->addTranslationEntry('test.new_key', 'Teks Baru', 'New Text');

        // Reload cache and check again
        $this->migrator->reloadTranslationCache();
        $this->assertTrue($this->migrator->keyExists('test.new_key'));
    }

    /** @test */
    public function it_can_find_duplicate_keys_by_text()
    {
        // Add a translation entry
        $this->migrator->addTranslationEntry('app.save', 'Simpan', 'Save');
        $this->migrator->reloadTranslationCache();

        // Try to find duplicate
        $duplicateKey = $this->migrator->findDuplicateKey('Simpan');

        $this->assertEquals('app.save', $duplicateKey);
    }

    /** @test */
    public function it_returns_null_when_no_duplicate_found()
    {
        $duplicateKey = $this->migrator->findDuplicateKey('Teks Yang Tidak Ada');

        $this->assertNull($duplicateKey);
    }

    /** @test */
    public function it_can_add_translation_entries_to_both_locales()
    {
        $key = 'test.greeting';
        $indonesianText = 'Halo';
        $englishText = 'Hello';

        $this->migrator->addTranslationEntry($key, $indonesianText, $englishText);

        // Check cache
        $cache = $this->migrator->getTranslationCache();
        $this->assertArrayHasKey('id', $cache);
        $this->assertArrayHasKey('en', $cache);
        $this->assertEquals($indonesianText, $cache['id'][$key]);
        $this->assertEquals($englishText, $cache['en'][$key]);
    }

    /** @test */
    public function it_can_migrate_a_file_with_findings()
    {
        // Create a test blade file
        $filePath = $this->testViewsPath . '/test.blade.php';
        $content = '<h1>Karyawan</h1><p>Data absensi</p>';
        File::put($filePath, $content);

        // Create findings
        $findings = [
            [
                'file' => 'tests/fixtures/views/test.blade.php',
                'line' => 1,
                'text' => 'Karyawan',
                'context' => '<h1>Karyawan</h1>',
            ],
            [
                'file' => 'tests/fixtures/views/test.blade.php',
                'line' => 1,
                'text' => 'Data absensi',
                'context' => '<p>Data absensi</p>',
            ],
        ];

        // Migrate the file
        $result = $this->migrator->migrateFile($filePath, $findings);

        $this->assertInstanceOf(MigrationResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals(2, $result->replacedCount);
        $this->assertGreaterThan(0, $result->keysCreated);
    }

    /** @test */
    public function it_returns_error_when_file_not_found()
    {
        $filePath = $this->testViewsPath . '/nonexistent.blade.php';
        $findings = [];

        $result = $this->migrator->migrateFile($filePath, $findings);

        $this->assertFalse($result->success);
        $this->assertNotEmpty($result->errors);
        $this->assertStringContainsString('File not found', $result->errors[0]);
    }

    /** @test */
    public function it_reuses_existing_keys_instead_of_creating_duplicates()
    {
        // Add an existing translation
        $this->migrator->addTranslationEntry('app.save', 'Simpan', 'Save');
        $this->migrator->reloadTranslationCache();

        // Create a test blade file with the same text
        $filePath = $this->testViewsPath . '/test.blade.php';
        $content = '<button>Simpan</button>';
        File::put($filePath, $content);

        $findings = [
            [
                'file' => 'tests/fixtures/views/test.blade.php',
                'line' => 1,
                'text' => 'Simpan',
                'context' => '<button>Simpan</button>',
            ],
        ];

        // Migrate the file
        $result = $this->migrator->migrateFile($filePath, $findings);

        $this->assertTrue($result->success);
        $this->assertEquals(1, $result->replacedCount);
        $this->assertEquals(1, $result->keysReused);
        $this->assertEquals(0, $result->keysCreated);
    }

    /** @test */
    public function it_handles_text_with_special_characters()
    {
        $content = '<p>Apakah Anda yakin?</p>';
        $text = 'Apakah Anda yakin?';
        $key = 'alert.confirm';

        $result = $this->migrator->replaceWithKey($content, $text, $key);

        $this->assertStringContainsString("{{ __('alert.confirm') }}", $result);
    }

    /** @test */
    public function it_handles_text_in_double_quotes()
    {
        $content = 'placeholder="Masukkan nama"';
        $text = 'Masukkan nama';
        $key = 'form.placeholder.name';

        $result = $this->migrator->replaceWithKey($content, $text, $key);

        $this->assertStringContainsString("{{ __('form.placeholder.name') }}", $result);
    }

    /** @test */
    public function it_handles_text_in_single_quotes()
    {
        $content = "title='Data Karyawan'";
        $text = 'Data Karyawan';
        $key = 'app.employee_data';

        $result = $this->migrator->replaceWithKey($content, $text, $key);

        $this->assertStringContainsString("{{ __('app.employee_data') }}", $result);
    }

    /** @test */
    public function migration_result_provides_summary()
    {
        $result = new MigrationResult(
            'test.blade.php',
            5,
            3,
            2,
            [],
            true
        );

        $summary = $result->getSummary();

        $this->assertStringContainsString('test.blade.php', $summary);
        $this->assertStringContainsString('5 replacements', $summary);
        $this->assertStringContainsString('3 keys created', $summary);
        $this->assertStringContainsString('2 keys reused', $summary);
    }

    /** @test */
    public function migration_result_can_add_errors()
    {
        $result = new MigrationResult('test.blade.php');

        $this->assertTrue($result->success);
        $this->assertEmpty($result->errors);

        $result->addError('Test error');

        $this->assertFalse($result->success);
        $this->assertCount(1, $result->errors);
        $this->assertEquals('Test error', $result->errors[0]);
    }

    /** @test */
    public function migration_result_converts_to_array()
    {
        $result = new MigrationResult(
            'test.blade.php',
            5,
            3,
            2,
            ['error1'],
            false
        );

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test.blade.php', $array['file_path']);
        $this->assertEquals(5, $array['replaced_count']);
        $this->assertEquals(3, $array['keys_created']);
        $this->assertEquals(2, $array['keys_reused']);
        $this->assertFalse($array['success']);
        $this->assertCount(1, $array['errors']);
    }
}
