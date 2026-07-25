<?php

/**
 * The outcome of one validator run against one resource.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Fields align with `oxy_validation_results` in docs/25-Database-Schema.md
 * (resource, validator, status, message, execution_time) — this DTO is
 * what will eventually be persisted there, once a DB-infra phase adds
 * that table; for now it only ever exists in memory for the duration
 * of a single REST request.
 */
final class ValidationResult
{
    public function __construct(
        public readonly string $resourceId,
        public readonly string $validator,
        public readonly ValidationStatus $status,
        public readonly string $message,
        public readonly float $executionTimeMs
    ) {
    }

    /**
     * @return array{
     *     resource_id: string,
     *     validator: string,
     *     status: string,
     *     message: string,
     *     execution_time_ms: float
     * }
     */
    public function toArray(): array
    {
        return [
            'resource_id' => $this->resourceId,
            'validator' => $this->validator,
            'status' => $this->status->value,
            'message' => $this->message,
            'execution_time_ms' => $this->executionTimeMs,
        ];
    }
}
