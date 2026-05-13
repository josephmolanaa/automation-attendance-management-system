<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Translation\HardcodedTextAuditor;
use App\Services\Translation\AuditReport;
use Illuminate\Support\Facades\File;

class HardcodedTextAuditorTest extends TestCase
{
    private HardcodedTextAuditor $auditor;
    private string $testViewsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditor = new HardcodedTextAuditor();
        $this->testViewsPath = base_path('tests/fixtures/views');
    }

    protected function tearDown(): void
    {
        // Clean up test fixtures if they exist
        if (File::exists($this->testViewsPath)) {
            File::deleteDirectory($this->testViewsPath);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_can_detect_hardcoded_indonesian_text()
    {
        // Create a test blade file with hardcoded Indonesian text
        $this->createTestBladeFile('test.blade.php', '<h1>Karyawan dan Absensi</h1>');

        $report = $this->auditor->scan('tests/fixtures/views');

        $this->assertInstanceOf(AuditReport::class, $report);
        $this->assertGreaterThan(0, $report->totalCount);
    }

    /** @test */
    public function it_excludes_text_within_translation_functions()
    {
        // Create a test blade file with translated text
        $content = '<h1>{{ __("app.employees") }}</h1>';
        $this->createTestBladeFile('translated.blade.php', $content);

        $report = $this->auditor->scan('tests/fixtures/views');

        // Should not detect text within __() function
        $this->assertEquals(0, $report->totalCount);
    }

    /** @test */
    public function it_excludes_html_comments()
    {
        // Create a test blade file with Indonesian text in comments
        $content = '<!-- Ini adalah karyawan dan absensi --><h1>Test</h1>';
        $this->createTestBladeFile('comments.blade.php', $content);

        $report = $this->auditor->scan('tests/fixtures/views');

        // Should not detect text in comments
        $this->assertEquals(0, $report->totalCount);
    }

    /** @test */
    public function it_excludes_php_comments()
    {
        // Create a test blade file with Indonesian text in PHP comments
        $content = "<?php\n// Ini adalah karyawan dan absensi\n?>\n<h1>Test</h1>";
        $this->createTestBladeFile('php-comments.blade.php', $content);

        $report = $this->auditor->scan('tests/fixtures/views');

        // Should not detect text in PHP comments
        $this->assertEquals(0, $report->totalCount);
    }

    /** @test */
    public function it_can_categorize_findings_by_priority()
    {
        // Create test files in different priority paths
        $this->createTestBladeFile('layouts/sidebar.blade.php', '<span>Karyawan</span>');
        $this->createTestBladeFile('forms/employee.blade.php', '<label>Nama Karyawan</label>');
        $this->createTestBladeFile('errors/404.blade.php', '<p>Halaman tidak ditemukan</p>');

        $report = $this->auditor->scan('tests/fixtures/views');
        $categorized = $this->auditor->categorizePriority($report->findings);

        $this->assertArrayHasKey('high', $categorized);
        $this->assertArrayHasKey('medium', $categorized);
        $this->assertArrayHasKey('low', $categorized);
    }

    /** @test */
    public function it_can_suggest_translation_key_names()
    {
        $text = 'Nama Karyawan';
        $context = 'resources/views/forms/employee.blade.php';

        $suggestedKey = $this->auditor->suggestKeyName($text, $context);

        $this->assertStringContainsString('form.label', $suggestedKey);
        $this->assertStringContainsString('nama_karyawan', $suggestedKey);
    }

    /** @test */
    public function it_detects_common_indonesian_words()
    {
        $testCases = [
            'Data karyawan dan absensi' => true,
            'Simpan atau batal' => true,
            'Apakah anda yakin?' => true,
            'Employee List' => false,
            'Save or Cancel' => false,
        ];

        foreach ($testCases as $text => $shouldDetect) {
            $this->createTestBladeFile('test_' . md5($text) . '.blade.php', "<p>{$text}</p>");
        }

        $report = $this->auditor->scan('tests/fixtures/views');

        // Should detect Indonesian text but not English text
        $this->assertGreaterThan(0, $report->totalCount);
    }

    /** @test */
    public function it_generates_markdown_report()
    {
        $this->createTestBladeFile('test.blade.php', '<h1>Karyawan dan Absensi</h1>');

        $report = $this->auditor->scan('tests/fixtures/views');
        $markdown = $report->toMarkdown();

        $this->assertStringContainsString('# Hardcoded Text Audit Report', $markdown);
        $this->assertStringContainsString('Total Findings:', $markdown);
    }

    /** @test */
    public function it_generates_json_report()
    {
        $this->createTestBladeFile('test.blade.php', '<h1>Karyawan dan Absensi</h1>');

        $report = $this->auditor->scan('tests/fixtures/views');
        $json = $report->toJson();

        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('findings', $data);
    }

    /**
     * Helper method to create test blade files
     */
    private function createTestBladeFile(string $filename, string $content): void
    {
        $filePath = $this->testViewsPath . '/' . $filename;
        $directory = dirname($filePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($filePath, $content);
    }
}
