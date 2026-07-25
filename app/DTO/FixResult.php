<?php

/**
 * The outcome of one Auto Fix attempt.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Fields per docs/18-AutoFix-Engine.md's "FIX REPORT" section, narrowed
 * to what this phase's engines can populate — Old Value/New Value/
 * Impact/Score Improvement need a diff/scoring-delta model beyond this
 * phase's scope.
 */
final class FixResult
{
    public function __construct(
        public readonly string $generatorId,
        public readonly bool $success,
        public readonly int $version,
        public readonly string $message,
        public readonly bool $pending = false
    ) {
    }

    /**
     * @return array{
     *     generator_id: string,
     *     success: bool,
     *     version: int,
     *     message: string,
     *     pending: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'generator_id' => $this->generatorId,
            'success' => $this->success,
            'version' => $this->version,
            'message' => $this->message,
            'pending' => $this->pending,
        ];
    }
}
