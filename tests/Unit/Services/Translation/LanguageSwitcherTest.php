<?php

namespace Tests\Unit\Services\Translation;

use Tests\TestCase;
use App\Services\Translation\LanguageSwitcher;
use App\Services\Translation\SwitchResult;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

class LanguageSwitcherTest extends TestCase
{
    protected LanguageSwitcher $switcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->switcher = new LanguageSwitcher();
    }

    /** @test */
    public function it_can_switch_to_supported_locale()
    {
        $result = $this->switcher->switchLocale('en');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('en', $result->getLocale());
        $this->assertNull($result->getError());
        $this->assertFalse($result->isBlocked());
        $this->assertEquals('en', App::getLocale());
    }

    /** @test */
    public function it_rejects_unsupported_locale()
    {
        $result = $this->switcher->switchLocale('fr');

        $this->assertFalse($result->isSuccessful());
        $this->assertNotNull($result->getError());
        $this->assertStringContainsString('not supported', $result->getError());
    }

    /** @test */
    public function it_stores_preference_in_session()
    {
        $this->switcher->switchLocale('en');

        $this->assertEquals('en', Session::get('locale'));
    }

    /** @test */
    public function it_returns_current_locale()
    {
        App::setLocale('id');
        $this->assertEquals('id', $this->switcher->getCurrentLocale());

        App::setLocale('en');
        $this->assertEquals('en', $this->switcher->getCurrentLocale());
    }

    /** @test */
    public function it_returns_default_locale()
    {
        $this->assertEquals('id', $this->switcher->getDefaultLocale());
    }

    /** @test */
    public function it_checks_if_locale_is_supported()
    {
        $this->assertTrue($this->switcher->isLocaleSupported('id'));
        $this->assertTrue($this->switcher->isLocaleSupported('en'));
        $this->assertFalse($this->switcher->isLocaleSupported('fr'));
        $this->assertFalse($this->switcher->isLocaleSupported('es'));
    }

    /** @test */
    public function it_returns_supported_locales()
    {
        $locales = $this->switcher->getSupportedLocales();

        $this->assertIsArray($locales);
        $this->assertContains('id', $locales);
        $this->assertContains('en', $locales);
    }

    /** @test */
    public function switch_result_can_be_converted_to_array()
    {
        $result = new SwitchResult(
            success: true,
            locale: 'en',
            error: null,
            blocked: false
        );

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertTrue($array['success']);
        $this->assertEquals('en', $array['locale']);
        $this->assertNull($array['error']);
        $this->assertFalse($array['blocked']);
    }

    /** @test */
    public function it_switches_to_indonesian_by_default()
    {
        // Clear any existing locale
        Session::forget('locale');
        
        $result = $this->switcher->switchLocale('id');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('id', $result->getLocale());
        $this->assertEquals('id', App::getLocale());
    }

    /** @test */
    public function it_can_switch_between_locales_multiple_times()
    {
        // Switch to English
        $result1 = $this->switcher->switchLocale('en');
        $this->assertTrue($result1->isSuccessful());
        $this->assertEquals('en', App::getLocale());

        // Switch back to Indonesian
        $result2 = $this->switcher->switchLocale('id');
        $this->assertTrue($result2->isSuccessful());
        $this->assertEquals('id', App::getLocale());

        // Switch to English again
        $result3 = $this->switcher->switchLocale('en');
        $this->assertTrue($result3->isSuccessful());
        $this->assertEquals('en', App::getLocale());
    }
}
