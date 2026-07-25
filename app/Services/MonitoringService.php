<?php

/**
 * Central Monitoring Engine: change detection, revalidation, notification.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\ChangeType;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\MonitoringEvent;
use OxyAI\DTO\NotificationPriority;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/20-Monitoring-Engine.md's pipeline (Scheduler → Resource
 * Scanner → Change Detection → Validation Engine → Impact Analysis →
 * Notifications → Historical Database → Dashboard): `Core/Scheduler.php`
 * doesn't exist yet, so there is no automatic periodic trigger — `start()`
 * arms monitoring and takes a baseline snapshot, and `scan()` (called
 * manually via REST, standing in for what a real Scheduler would call
 * on a timer) is the Resource Scanner + Change Detection + Validation +
 * Notification steps. A resource's "value" for change detection is a
 * fingerprint of its Discovery Map metadata plus its generated content
 * (when it has a registered Generator) — the same underlying data
 * Discovery/Generation already produce, not a new scanning mechanism.
 *
 * Everything is in-memory only, per every prior engine's documented
 * limitation (no `oxy_*` table exists yet): a fresh request gets a
 * fresh, un-armed `MonitoringService`, so `start()` must be called
 * again before `scan()` will detect anything.
 */
final class MonitoringService
{
    private bool $active = false;
    private ?string $lastCheckedAt = null;

    /** @var array<string, string> */
    private array $fingerprints = [];

    /** @var array<int, MonitoringEvent> */
    private array $events = [];

    public function __construct(
        private readonly DiscoveryService $discovery,
        private readonly ValidationService $validation,
        private readonly GenerationService $generation
    ) {
    }

    public function start(): void
    {
        $this->discovery->reset();
        $this->fingerprints = $this->snapshot();
        $this->active = true;
        $this->lastCheckedAt = gmdate('c');

        do_action('oxy_ai_monitoring_started');
    }

    public function stop(): void
    {
        $this->active = false;

        do_action('oxy_ai_monitoring_stopped');
    }

    public function reset(): void
    {
        $this->active = false;
        $this->fingerprints = [];
        $this->events = [];
        $this->lastCheckedAt = null;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function lastCheckedAt(): ?string
    {
        return $this->lastCheckedAt;
    }

    public function resourcesTracked(): int
    {
        return count($this->fingerprints);
    }

    /**
     * @return array<int, MonitoringEvent>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * Diffs the current Discovery Map against the last-known
     * fingerprints. Every resource whose fingerprint differs (or is
     * new) is revalidated immediately and turned into a
     * `MonitoringEvent`; every previously-tracked resource no longer
     * discovered produces a Deleted event. A no-op (returns an empty
     * array) while monitoring hasn't been started — there is no
     * baseline to diff against.
     *
     * @return array<int, MonitoringEvent>
     */
    public function scan(): array
    {
        if (!$this->active) {
            return [];
        }

        do_action('oxy_ai_monitoring_scan_started');

        $this->discovery->reset();
        $current = $this->discovery->map();
        $currentFingerprints = [];
        $changes = [];

        foreach ($current as $resourceId => $resource) {
            $fingerprint = $this->fingerprint($resource);
            $currentFingerprints[$resourceId] = $fingerprint;

            $previous = $this->fingerprints[$resourceId] ?? null;

            if ($previous === $fingerprint) {
                continue;
            }

            $type = $previous === null ? ChangeType::Created : ChangeType::Modified;
            $changes[] = $this->recordChange($resource, $type);
        }

        foreach ($this->fingerprints as $resourceId => $fingerprint) {
            if (isset($current[$resourceId])) {
                continue;
            }

            $changes[] = $this->recordDeletion($resourceId);
        }

        $this->fingerprints = $currentFingerprints;
        $this->lastCheckedAt = gmdate('c');

        do_action('oxy_ai_monitoring_scan_finished', $changes);

        return $changes;
    }

    /**
     * @return array<string, string>
     */
    private function snapshot(): array
    {
        $fingerprints = [];

        foreach ($this->discovery->map() as $resourceId => $resource) {
            $fingerprints[$resourceId] = $this->fingerprint($resource);
        }

        return $fingerprints;
    }

    private function fingerprint(DiscoveredResource $resource): string
    {
        $content = $this->generation->has($resource->module) ? $this->generation->generate($resource->module) : '';

        return hash('sha256', (string) wp_json_encode([
            'type' => $resource->type,
            'location' => $resource->location,
            'status' => $resource->status,
            'version' => $resource->version,
            'health' => $resource->health,
            'dependencies' => $resource->dependencies,
            'content' => $content,
        ]));
    }

    private function recordChange(DiscoveredResource $resource, ChangeType $type): MonitoringEvent
    {
        $results = $this->validation->validate($resource);
        $priority = $this->severityOf($results);

        $event = new MonitoringEvent(
            resourceId: $resource->id,
            changeType: $type,
            results: $results,
            priority: $priority,
            message: sprintf('Resource "%s" was %s (%s).', $resource->id, $type->value, $priority->value),
            detectedAt: gmdate('c')
        );

        $this->events[] = $event;

        do_action('oxy_ai_resource_changed', $event);

        if ($priority !== NotificationPriority::Informational) {
            do_action('oxy_ai_notification_sent', $event);
        }

        return $event;
    }

    private function recordDeletion(string $resourceId): MonitoringEvent
    {
        $event = new MonitoringEvent(
            resourceId: $resourceId,
            changeType: ChangeType::Deleted,
            results: [],
            priority: NotificationPriority::Critical,
            message: sprintf('Resource "%s" is no longer discovered.', $resourceId),
            detectedAt: gmdate('c')
        );

        $this->events[] = $event;

        do_action('oxy_ai_resource_changed', $event);
        do_action('oxy_ai_notification_sent', $event);

        return $event;
    }

    /**
     * @param array<int, ValidationResult> $results
     */
    private function severityOf(array $results): NotificationPriority
    {
        foreach ($results as $result) {
            if ($result->status === ValidationStatus::Fail) {
                return NotificationPriority::Critical;
            }
        }

        foreach ($results as $result) {
            if ($result->status === ValidationStatus::Warning) {
                return NotificationPriority::Medium;
            }
        }

        return NotificationPriority::Informational;
    }
}
