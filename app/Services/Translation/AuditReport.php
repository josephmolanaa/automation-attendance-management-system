<?php

namespace App\Services\Translation;

class AuditReport
{
    /**
     * @var array All findings from the audit
     */
    public array $findings;

    /**
     * @var int Total count of findings
     */
    public int $totalCount;

    /**
     * @var array Count by priority [high => count, medium => count, low => count]
     */
    public array $byPriority;

    /**
     * Create a new AuditReport instance
     * 
     * @param array $findings Array of findings
     */
    public function __construct(array $findings)
    {
        $this->findings = $findings;
        $this->totalCount = count($findings);
        $this->byPriority = $this->calculatePriorityCount($findings);
    }

    /**
     * Calculate count by priority
     * 
     * @param array $findings Array of findings
     * @return array Priority counts
     */
    private function calculatePriorityCount(array $findings): array
    {
        $counts = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($findings as $finding) {
            $priority = $finding['priority'] ?? 'medium';
            if (isset($counts[$priority])) {
                $counts[$priority]++;
            }
        }

        return $counts;
    }

    /**
     * Generate markdown report
     * 
     * @return string Markdown formatted report
     */
    public function toMarkdown(): string
    {
        $markdown = "# Hardcoded Text Audit Report\n\n";
        $markdown .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
        $markdown .= "## Summary\n\n";
        $markdown .= "- **Total Findings:** {$this->totalCount}\n";
        $markdown .= "- **High Priority:** {$this->byPriority['high']}\n";
        $markdown .= "- **Medium Priority:** {$this->byPriority['medium']}\n";
        $markdown .= "- **Low Priority:** {$this->byPriority['low']}\n\n";

        if (empty($this->findings)) {
            $markdown .= "✅ No hardcoded Indonesian text found!\n";
            return $markdown;
        }

        // Group findings by priority
        $byPriority = [
            'high' => [],
            'medium' => [],
            'low' => [],
        ];

        foreach ($this->findings as $finding) {
            $priority = $finding['priority'] ?? 'medium';
            $byPriority[$priority][] = $finding;
        }

        // Output findings by priority
        foreach (['high', 'medium', 'low'] as $priority) {
            if (empty($byPriority[$priority])) {
                continue;
            }

            $priorityLabel = ucfirst($priority);
            $markdown .= "## {$priorityLabel} Priority Findings\n\n";

            // Group by file
            $byFile = [];
            foreach ($byPriority[$priority] as $finding) {
                $file = $finding['file'];
                if (!isset($byFile[$file])) {
                    $byFile[$file] = [];
                }
                $byFile[$file][] = $finding;
            }

            foreach ($byFile as $file => $findings) {
                $markdown .= "### {$file}\n\n";
                $markdown .= "| Line | Text | Context |\n";
                $markdown .= "|------|------|----------|\n";

                foreach ($findings as $finding) {
                    $text = $this->escapeMarkdown($finding['text']);
                    $context = $this->escapeMarkdown($this->truncate($finding['context'], 80));
                    $markdown .= "| {$finding['line']} | {$text} | {$context} |\n";
                }

                $markdown .= "\n";
            }
        }

        return $markdown;
    }

    /**
     * Generate JSON report
     * 
     * @return string JSON formatted report
     */
    public function toJson(): string
    {
        $data = [
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_count' => $this->totalCount,
                'by_priority' => $this->byPriority,
            ],
            'findings' => $this->findings,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Escape markdown special characters
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeMarkdown(string $text): string
    {
        $specialChars = ['\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '#', '+', '-', '.', '!', '|'];
        
        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }

        return $text;
    }

    /**
     * Truncate text to specified length
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3, 'UTF-8') . '...';
    }

    /**
     * Get findings by priority
     * 
     * @param string $priority Priority level (high, medium, low)
     * @return array Findings for the specified priority
     */
    public function getFindingsByPriority(string $priority): array
    {
        return array_filter($this->findings, function($finding) use ($priority) {
            return ($finding['priority'] ?? 'medium') === $priority;
        });
    }

    /**
     * Get findings by file
     * 
     * @param string $file File path
     * @return array Findings for the specified file
     */
    public function getFindingsByFile(string $file): array
    {
        return array_filter($this->findings, function($finding) use ($file) {
            return $finding['file'] === $file;
        });
    }

    /**
     * Get unique files with findings
     * 
     * @return array Array of unique file paths
     */
    public function getUniqueFiles(): array
    {
        $files = array_map(function($finding) {
            return $finding['file'];
        }, $this->findings);

        return array_unique($files);
    }
}
