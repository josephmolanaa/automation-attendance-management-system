<?php

namespace Tests\Unit\Services\Translation;

use App\Services\Translation\TerminologyDictionary;
use Exception;
use Tests\TestCase;

/**
 * Test TerminologyDictionary class
 * 
 * Validates: Requirements 2.1, 2.2, 2.3
 */
class TerminologyDictionaryTest extends TestCase
{
    protected TerminologyDictionary $dictionary;
    protected string $dictionaryPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dictionary = new TerminologyDictionary();
        $this->dictionaryPath = base_path('docs/terminology-dictionary.md');
    }

    /**
     * Test dictionary can be loaded from file
     * 
     * @test
     */
    public function it_loads_dictionary_from_file(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $this->assertTrue($this->dictionary->isLoaded());
    }

    /**
     * Test loading non-existent file throws exception
     * 
     * @test
     */
    public function it_throws_exception_for_non_existent_file(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dictionary file not found');
        
        $this->dictionary->load('non-existent-file.md');
    }

    /**
     * Test getting standard term for a concept
     * Validates Requirement 2.1: Dictionary defines exactly one standard term per concept
     * 
     * @test
     */
    public function it_returns_standard_term_for_concept(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        // Test core business terms from Requirement 2.2
        $this->assertEquals('Karyawan', $this->dictionary->getStandardTerm('employee'));
        $this->assertEquals('Absensi', $this->dictionary->getStandardTerm('attendance'));
        $this->assertEquals('Jadwal', $this->dictionary->getStandardTerm('schedule'));
        $this->assertEquals('Lembur', $this->dictionary->getStandardTerm('overtime'));
    }

    /**
     * Test getting deprecated terms for a concept
     * Validates Requirement 2.3: Dictionary specifies which terms to replace
     * 
     * @test
     */
    public function it_returns_deprecated_terms_for_concept(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        // Test deprecated terms from Requirement 2.2
        $employeeDeprecated = $this->dictionary->getDeprecatedTerms('employee');
        $this->assertContains('Pegawai', $employeeDeprecated);
        
        $attendanceDeprecated = $this->dictionary->getDeprecatedTerms('attendance');
        $this->assertContains('Kehadiran', $attendanceDeprecated);
        
        $leaveDeprecated = $this->dictionary->getDeprecatedTerms('leave');
        $this->assertContains('Izin (for leave)', $leaveDeprecated);
    }

    /**
     * Test getting deprecated terms for concept without deprecated terms
     * 
     * @test
     */
    public function it_returns_empty_array_for_concept_without_deprecated_terms(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        // Some concepts might not have deprecated terms
        $deprecated = $this->dictionary->getDeprecatedTerms('dashboard');
        $this->assertIsArray($deprecated);
    }

    /**
     * Test getting standard term for non-existent concept throws exception
     * 
     * @test
     */
    public function it_throws_exception_for_non_existent_concept(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Concept not found in dictionary');
        
        $this->dictionary->getStandardTerm('non_existent_concept');
    }

    /**
     * Test navigation term uniqueness validation
     * Validates Requirement 9.4: Navigation concepts must have unique terms
     * 
     * @test
     */
    public function it_validates_navigation_term_uniqueness(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        // Test that a unique term is valid
        $this->assertTrue($this->dictionary->isNavigationTermUnique('home', 'Beranda'));
        
        // Test that using an existing navigation term for a different concept is invalid
        $this->assertFalse($this->dictionary->isNavigationTermUnique('some_other_concept', 'Beranda'));
    }

    /**
     * Test suggesting English translation for Indonesian term
     * 
     * @test
     */
    public function it_suggests_english_translation_for_indonesian_term(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $this->assertEquals('Employee', $this->dictionary->suggestEnglishTranslation('Karyawan'));
        $this->assertEquals('Attendance', $this->dictionary->suggestEnglishTranslation('Absensi'));
        $this->assertEquals('Schedule', $this->dictionary->suggestEnglishTranslation('Jadwal'));
        $this->assertEquals('Overtime', $this->dictionary->suggestEnglishTranslation('Lembur'));
    }

    /**
     * Test suggesting English translation for unknown term returns the term itself
     * 
     * @test
     */
    public function it_returns_term_itself_for_unknown_translation(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $unknownTerm = 'UnknownTerm';
        $this->assertEquals($unknownTerm, $this->dictionary->suggestEnglishTranslation($unknownTerm));
    }

    /**
     * Test getting all standard terms
     * 
     * @test
     */
    public function it_returns_all_standard_terms(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $allTerms = $this->dictionary->getAllStandardTerms();
        
        $this->assertIsArray($allTerms);
        $this->assertNotEmpty($allTerms);
        $this->assertArrayHasKey('employee', $allTerms);
        $this->assertArrayHasKey('attendance', $allTerms);
    }

    /**
     * Test getting all navigation terms
     * 
     * @test
     */
    public function it_returns_all_navigation_terms(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $navTerms = $this->dictionary->getAllNavigationTerms();
        
        $this->assertIsArray($navTerms);
        $this->assertNotEmpty($navTerms);
        $this->assertArrayHasKey('home', $navTerms);
        $this->assertArrayHasKey('dashboard', $navTerms);
    }

    /**
     * Test checking if concept exists
     * 
     * @test
     */
    public function it_checks_if_concept_exists(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $this->assertTrue($this->dictionary->hasConcept('employee'));
        $this->assertTrue($this->dictionary->hasConcept('attendance'));
        $this->assertFalse($this->dictionary->hasConcept('non_existent_concept'));
    }

    /**
     * Test methods throw exception when dictionary not loaded
     * 
     * @test
     */
    public function it_throws_exception_when_not_loaded(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dictionary not loaded');
        
        $this->dictionary->getStandardTerm('employee');
    }

    /**
     * Test dictionary contains at least 50 core business terms
     * Validates Requirement 2.5: Dictionary includes at least 50 core business terms
     * 
     * @test
     */
    public function it_contains_at_least_50_core_business_terms(): void
    {
        $this->dictionary->load($this->dictionaryPath);
        
        $allTerms = $this->dictionary->getAllStandardTerms();
        
        $this->assertGreaterThanOrEqual(50, count($allTerms), 
            'Dictionary should contain at least 50 core business terms');
    }
}
