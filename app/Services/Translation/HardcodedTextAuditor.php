<?php

namespace App\Services\Translation;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class HardcodedTextAuditor
{
    /**
     * Common Indonesian words used to detect Indonesian text patterns
     */
    private const INDONESIAN_PATTERNS = [
        'dan', 'atau', 'untuk', 'yang', 'dengan', 'dari', 'pada', 'adalah',
        'akan', 'telah', 'sudah', 'belum', 'tidak', 'bukan', 'jika', 'kalau',
        'karena', 'sebab', 'oleh', 'kepada', 'dalam', 'antara', 'setiap',
        'semua', 'beberapa', 'banyak', 'sedikit', 'lebih', 'kurang',
        'karyawan', 'pegawai', 'absensi', 'kehadiran', 'jadwal', 'lembur',
        'cuti', 'izin', 'laporan', 'data', 'tanggal', 'waktu', 'hari',
        'bulan', 'tahun', 'nama', 'alamat', 'telepon', 'email',
        'simpan', 'hapus', 'edit', 'tambah', 'batal', 'cari', 'lihat',
        'berhasil', 'gagal', 'error', 'sukses', 'peringatan', 'informasi',
        'apakah', 'anda', 'yakin', 'ingin', 'menghapus', 'menyimpan',
        'wajib', 'diisi', 'harus', 'boleh', 'dapat', 'bisa'
    ];

    /**
     * Scan directory for hardcoded Indonesian text
     * 
     * @param string $directory Path to scan (default: resources/views)
     * @return AuditReport
     */
    public function scan(string $directory = 'resources/views'): AuditReport
    {
        $basePath = base_path($directory);
        $findings = [];

        if (!is_dir($basePath)) {
            return new AuditReport($findings);
        }

        // Recursively iterate through all blade files
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );
        
        $bladeFiles = new RegexIterator($iterator, '/^.+\.blade\.php$/i');

        foreach ($bladeFiles as $file) {
            $filePath = $file->getPathname();
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);
            
            $fileFindings = $this->scanFile($filePath, $relativePath);
            $findings = array_merge($findings, $fileFindings);
        }

        return new AuditReport($findings);
    }

    /**
     * Scan a single file for hardcoded Indonesian text
     * 
     * @param string $filePath Absolute file path
     * @param string $relativePath Relative file path for reporting
     * @return array Array of findings
     */
    private function scanFile(string $filePath, string $relativePath): array
    {
        $findings = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return $findings;
        }

        foreach ($lines as $lineNumber => $line) {
            $lineNumber++; // Convert to 1-based indexing

            // Skip if line is a comment
            if ($this->isComment($line)) {
                continue;
            }

            // Find all text that might be hardcoded
            $potentialTexts = $this->extractPotentialTexts($line);

            foreach ($potentialTexts as $text) {
                // Skip if text is within translation functions
                if ($this->isWithinTranslationFunction($line, $text)) {
                    continue;
                }

                // Check if text contains Indonesian patterns
                if ($this->containsIndonesianText($text)) {
                    $findings[] = [
                        'file' => $relativePath,
                        'line' => $lineNumber,
                        'text' => $text,
                        'context' => trim($line),
                    ];
                }
            }
        }

        return $findings;
    }

    /**
     * Check if a line is a comment
     * 
     * @param string $line Line of code
     * @return bool
     */
    private function isComment(string $line): bool
    {
        $trimmed = trim($line);
        
        // HTML comments
        if (preg_match('/^\s*<!--/', $trimmed)) {
            return true;
        }

        // PHP single-line comments
        if (preg_match('/^\s*\/\//', $trimmed)) {
            return true;
        }

        // PHP multi-line comments
        if (preg_match('/^\s*\/\*/', $trimmed) || preg_match('/^\s*\*/', $trimmed)) {
            return true;
        }

        return false;
    }

    /**
     * Extract potential text strings from a line
     * 
     * @param string $line Line of code
     * @return array Array of potential text strings
     */
    private function extractPotentialTexts(string $line): array
    {
        $texts = [];

        // Match text within double quotes (excluding escaped quotes)
        preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/u', $line, $doubleQuotes);
        if (!empty($doubleQuotes[1])) {
            $texts = array_merge($texts, $doubleQuotes[1]);
        }

        // Match text within single quotes (excluding escaped quotes)
        preg_match_all("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/u", $line, $singleQuotes);
        if (!empty($singleQuotes[1])) {
            $texts = array_merge($texts, $singleQuotes[1]);
        }

        // Match text within HTML tags (between > and <)
        preg_match_all('/>([^<>]+)</u', $line, $htmlContent);
        if (!empty($htmlContent[1])) {
            foreach ($htmlContent[1] as $content) {
                $trimmed = trim($content);
                // Only include if it's not just whitespace or blade directives
                if (!empty($trimmed) && !preg_match('/^@\w+/', $trimmed) && !preg_match('/^\{\{/', $trimmed)) {
                    $texts[] = $trimmed;
                }
            }
        }

        return array_filter($texts, function($text) {
            // Filter out empty strings and very short strings (likely not meaningful text)
            return strlen(trim($text)) > 2;
        });
    }

    /**
     * Check if text is within a translation function call
     * 
     * @param string $line Line of code
     * @param string $text Text to check
     * @return bool
     */
    private function isWithinTranslationFunction(string $line, string $text): bool
    {
        // Escape special regex characters in the text
        $escapedText = preg_quote($text, '/');

        // Check for __() function
        if (preg_match('/\b__\s*\([^)]*' . $escapedText . '[^)]*\)/', $line)) {
            return true;
        }

        // Check for @lang() directive
        if (preg_match('/@lang\s*\([^)]*' . $escapedText . '[^)]*\)/', $line)) {
            return true;
        }

        // Check for trans() function
        if (preg_match('/\btrans\s*\([^)]*' . $escapedText . '[^)]*\)/', $line)) {
            return true;
        }

        // Check for {{ __() }} blade syntax
        if (preg_match('/\{\{\s*__\s*\([^}]*' . $escapedText . '[^}]*\)\s*\}\}/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Check if text contains Indonesian language patterns
     * 
     * @param string $text Text to check
     * @return bool
     */
    private function containsIndonesianText(string $text): bool
    {
        $lowerText = mb_strtolower($text, 'UTF-8');

        // Check for common Indonesian words
        foreach (self::INDONESIAN_PATTERNS as $pattern) {
            // Use word boundary to match whole words only
            if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/u', $lowerText)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Categorize findings by priority
     * 
     * @param array $findings Raw scan results
     * @return array Categorized findings [high => [], medium => [], low => []]
     */
    public function categorizePriority(array $findings): array
    {
        $categorized = [
            'high' => [],
            'medium' => [],
            'low' => [],
        ];

        $priorityPaths = config('translation.audit.priority_paths', [
            'high' => ['layouts', 'dashboard', 'partials/sidebar'],
            'medium' => ['forms', 'modals'],
            'low' => ['errors', 'emails'],
        ]);

        foreach ($findings as $finding) {
            $filePath = $finding['file'];
            $priority = 'medium'; // Default priority

            // Check high priority paths
            foreach ($priorityPaths['high'] as $highPath) {
                if (stripos($filePath, $highPath) !== false) {
                    $priority = 'high';
                    break;
                }
            }

            // Check low priority paths (only if not already high)
            if ($priority !== 'high') {
                foreach ($priorityPaths['low'] as $lowPath) {
                    if (stripos($filePath, $lowPath) !== false) {
                        $priority = 'low';
                        break;
                    }
                }
            }

            // Check medium priority paths (only if not already high or low)
            if ($priority === 'medium') {
                foreach ($priorityPaths['medium'] as $mediumPath) {
                    if (stripos($filePath, $mediumPath) !== false) {
                        $priority = 'medium';
                        break;
                    }
                }
            }

            $finding['priority'] = $priority;
            $categorized[$priority][] = $finding;
        }

        return $categorized;
    }

    /**
     * Generate suggested translation key names
     * 
     * @param string $text Hardcoded text
     * @param string $context File path and surrounding code
     * @return string Suggested key name
     */
    public function suggestKeyName(string $text, string $context): string
    {
        // Determine the domain/category based on context
        $domain = $this->determineDomain($context);

        // Clean and convert text to snake_case
        $key = $this->textToSnakeCase($text);

        // Limit key length
        if (strlen($key) > 50) {
            $key = substr($key, 0, 50);
        }

        return $domain . '.' . $key;
    }

    /**
     * Determine the domain/category from context
     * 
     * @param string $context File path or code context
     * @return string Domain prefix
     */
    private function determineDomain(string $context): string
    {
        $lowerContext = strtolower($context);

        if (stripos($lowerContext, 'sidebar') !== false || stripos($lowerContext, 'menu') !== false) {
            return 'nav';
        }

        if (stripos($lowerContext, 'button') !== false || stripos($lowerContext, 'btn') !== false) {
            return 'button';
        }

        if (stripos($lowerContext, 'form') !== false || stripos($lowerContext, 'label') !== false) {
            return 'form.label';
        }

        if (stripos($lowerContext, 'table') !== false || stripos($lowerContext, 'thead') !== false) {
            return 'table.column';
        }

        if (stripos($lowerContext, 'alert') !== false || stripos($lowerContext, 'swal') !== false) {
            return 'alert';
        }

        if (stripos($lowerContext, 'message') !== false || stripos($lowerContext, 'flash') !== false) {
            return 'message';
        }

        if (stripos($lowerContext, 'breadcrumb') !== false) {
            return 'breadcrumb';
        }

        return 'app';
    }

    /**
     * Convert text to snake_case for key naming
     * 
     * @param string $text Text to convert
     * @return string Snake case key
     */
    private function textToSnakeCase(string $text): string
    {
        // Remove special characters and extra spaces
        $text = preg_replace('/[^\w\s-]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Convert to lowercase and replace spaces with underscores
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace([' ', '-'], '_', $text);

        // Remove consecutive underscores
        $text = preg_replace('/_+/', '_', $text);

        // Remove leading/trailing underscores
        $text = trim($text, '_');

        return $text;
    }
}
