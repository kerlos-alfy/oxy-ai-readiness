<?php

/**
 * One detected change to one resource, produced by a Monitoring scan.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/20-Monitoring-Engine.md's "HISTORY" section (narrowed to
 * what's actually derivable without persisted storage or a real user
 * context — no `oxy_*` table exists yet, and a scan isn't necessarily
 * tied to a logged-in user): Timestamp, Resource, and the revalidation
 * results triggered by the change stand in for that section's fuller
 * Previous/Current Value + Impact + Recommendation + Rollback set,
 * which need a richer change-history store this project doesn't have.
 */
final class MonitoringEvent
{
    /**
     * @param array<int, ValidationResult> $results
     */
    public function __construct(
        public readonly string $resourceId,
        public readonly ChangeType $changeType,
        public readonly array $results,
        public readonly NotificationPriority $priority,
        public readonly string $message,
        public readonly string $detectedAt
    ) {
    }

    /**
     * @return array{
     *     resource_id: string,
     *     change_type: string,
     *     priority: string,
     *     message: string,
     *     detected_at: string,
     *     results: array<int, array{
     *         resource_id: string, validator: string, status: string,
     *         message: string, execution_time_ms: float
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'resource_id' => $this->resourceId,
            'change_type' => $this->changeType->value,
            'priority' => $this->priority->value,
            'message' => $this->message,
            'detected_at' => $this->detectedAt,
            'results' => array_map(
                static fn (ValidationResult $result): array => $result->toArray(),
                $this->results
            ),
        ];
    }
}
