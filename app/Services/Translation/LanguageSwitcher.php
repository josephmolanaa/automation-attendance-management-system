<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

/**
 * Class LanguageSwitcher
 * 
 * Handles language switching functionality with proper storage failure handling.
 * Blocks language switch entirely if storage fails per Requirement 10.3.
 * 
 * @package App\Services\Translation
 */
class LanguageSwitcher
{
    /**
     * @var array Supported locales
     */
    protected array $supportedLocales;

    /**
     * @var string Default locale
     */
    protected string $defaultLocale;

    /**
     * @var string Cookie name for locale storage
     */
    protected string $cookieName;

    /**
     * @var int Cookie lifetime in minutes
     */
    protected int $cookieLifetime;

    /**
     * @var bool Whether to block switch on storage failure
     */
    protected bool $blockOnStorageFailure;

    /**
     * Create a new LanguageSwitcher instance
     */
    public function __construct()
    {
        $this->supportedLocales = config('app.available_locales', ['id', 'en']);
        $this->defaultLocale = config('app.locale', 'id');
        $this->cookieName = config('translation.cookie_name', 'app_locale');
        $this->cookieLifetime = config('translation.cookie_lifetime', 525600); // 1 year
        $this->blockOnStorageFailure = config('translation.block_on_storage_failure', true);
    }

    /**
     * Switch application locale
     * Blocks switch entirely if storage fails (Requirement 10.3)
     *
     * @param string $locale Target locale ('id' or 'en')
     * @return SwitchResult
     */
    public function switchLocale(string $locale): SwitchResult
    {
        // Validate locale
        if (!in_array($locale, $this->supportedLocales)) {
            return new SwitchResult(
                success: false,
                locale: $this->getCurrentLocale(),
                error: "Locale '{$locale}' is not supported. Supported locales: " . implode(', ', $this->supportedLocales),
                blocked: false
            );
        }

        // Attempt to store preference
        $storageSuccess = $this->storePreference($locale);

        // If storage fails and blocking is enabled, block the switch
        if (!$storageSuccess && $this->blockOnStorageFailure) {
            Log::warning("Language switch blocked due to storage failure for locale: {$locale}");
            
            return new SwitchResult(
                success: false,
                locale: $this->getCurrentLocale(),
                error: 'Tidak dapat menyimpan preferensi bahasa. Pastikan cookies diaktifkan.',
                blocked: true
            );
        }

        // Set application locale
        App::setLocale($locale);

        Log::info("Language switched successfully to: {$locale}");

        return new SwitchResult(
            success: true,
            locale: $locale,
            error: null,
            blocked: false
        );
    }

    /**
     * Store locale preference in session and cookie
     *
     * @param string $locale Locale to store
     * @return bool Success status
     */
    public function storePreference(string $locale): bool
    {
        try {
            // Store in session
            Session::put('locale', $locale);

            // Verify session storage succeeded
            if (Session::get('locale') !== $locale) {
                Log::error("Session storage verification failed for locale: {$locale}");
                return false;
            }

            // Queue cookie for storage
            Cookie::queue(
                $this->cookieName,
                $locale,
                $this->cookieLifetime,
                '/',
                null,
                true, // secure
                true  // httpOnly
            );

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to store locale preference: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get current locale
     *
     * @return string Current locale code
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get default locale
     *
     * @return string Default locale ('id')
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Get supported locales
     *
     * @return array
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Check if a locale is supported
     *
     * @param string $locale
     * @return bool
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales);
    }

    /**
     * Get locale from request (URL parameter, session, or cookie)
     * Priority: URL parameter > Session > Cookie > Default
     *
     * @param \Illuminate\Http\Request|null $request
     * @return string
     */
    public function getLocaleFromRequest($request = null): string
    {
        if ($request) {
            // Check URL parameter
            $urlLocale = $request->get('lang');
            if ($urlLocale && $this->isLocaleSupported($urlLocale)) {
                return $urlLocale;
            }
        }

        // Check session
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && $this->isLocaleSupported($sessionLocale)) {
            return $sessionLocale;
        }

        // Check cookie
        if ($request) {
            $cookieLocale = $request->cookie($this->cookieName);
            if ($cookieLocale && $this->isLocaleSupported($cookieLocale)) {
                return $cookieLocale;
            }
        }

        // Return default
        return $this->defaultLocale;
    }
}
