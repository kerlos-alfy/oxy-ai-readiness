<?php

/**
 * An actionable recommendation derived from a validation issue.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Fields per docs/19-Recommendation-Engine.md's "RECOMMENDATION
 * OBJECT" section, narrowed to what's actually derivable from a
 * `ValidationResult` — Estimated Impact/Effort/Time, Documentation,
 * Related Issues, and Dependencies would need data (impact modeling,
 * doc links, issue-relationship graphs) this phase's engines don't
 * produce; inventing values for them would be exactly the kind of
 * placeholder data CLAUDE.md prohibits.
 */
final class Recommendation
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $category,
        public readonly string $priority,
        public readonly bool $autoFixAvailable
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     description: string,
     *     category: string,
     *     priority: string,
     *     auto_fix_available: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'auto_fix_available' => $this->autoFixAvailable,
        ];
    }
}
