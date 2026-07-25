<?php

/**
 * The outcome of publishing one generator's output.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Fields per docs/17-Generation-Engine.md's "VERSIONING" section
 * (Version, Timestamp, Checksum, Source, Rollback Point) — Author is
 * omitted, since nothing in this pipeline runs as a specific WordPress
 * user yet (Generation is triggered by a REST request gated on
 * capability, not tied to a persisted author record anywhere).
 */
final class GenerationResult
{
    public function __construct(
        public readonly string $generatorId,
        public readonly string $path,
        public readonly int $version,
        public readonly string $checksum,
        public readonly string $publishedAt
    ) {
    }

    /**
     * @return array{
     *     generator_id: string,
     *     path: string,
     *     version: int,
     *     checksum: string,
     *     published_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'generator_id' => $this->generatorId,
            'path' => $this->path,
            'version' => $this->version,
            'checksum' => $this->checksum,
            'published_at' => $this->publishedAt,
        ];
    }
}
