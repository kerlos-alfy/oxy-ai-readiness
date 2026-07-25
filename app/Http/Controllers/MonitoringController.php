<?php

/**
 * REST controller for the Monitoring Engine.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Http\Controllers;

use OxyAI\DTO\MonitoringEvent;
use OxyAI\Services\MonitoringService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Gated behind `manage_options`, same interim default as every other
 * controller so far. `/monitoring/scan` isn't in docs/20-Monitoring-Engine.md's
 * own REST list, but is exposed under its own accurate name since no
 * `Core/Scheduler.php` exists yet to call the detection pipeline
 * automatically — same precedent as Phase 6 exposing `/generation/rollback`
 * beyond docs/17's own list. `/monitoring/history` is not implemented:
 * without persisted storage it would return exactly what `/monitoring/events`
 * already does, and pretending otherwise would fake a distinction that
 * doesn't exist yet. See DECISIONS.md.
 */
final class MonitoringController
{
    public function __construct(private readonly MonitoringService $monitoring)
    {
    }

    public function authorize(): bool
    {
        return current_user_can('manage_options');
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(
            ['success' => true, 'data' => array_merge($this->statusArray(), ['events' => $this->eventsArray()])],
            200
        );
    }

    public function status(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(['success' => true, 'data' => $this->statusArray()], 200);
    }

    public function events(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(['success' => true, 'data' => $this->eventsArray()], 200);
    }

    public function start(WP_REST_Request $request): WP_REST_Response
    {
        $this->monitoring->start();

        return new WP_REST_Response(['success' => true, 'data' => $this->statusArray()], 200);
    }

    public function stop(WP_REST_Request $request): WP_REST_Response
    {
        $this->monitoring->stop();

        return new WP_REST_Response(['success' => true, 'data' => $this->statusArray()], 200);
    }

    public function reset(WP_REST_Request $request): WP_REST_Response
    {
        $this->monitoring->reset();

        return new WP_REST_Response(['success' => true, 'data' => $this->statusArray()], 200);
    }

    public function scan(WP_REST_Request $request): WP_REST_Response
    {
        $events = $this->eventsToArray($this->monitoring->scan());

        return new WP_REST_Response(['success' => true, 'data' => $events], 200);
    }

    /**
     * @return array{active: bool, last_checked_at: ?string, resources_tracked: int}
     */
    private function statusArray(): array
    {
        return [
            'active' => $this->monitoring->isActive(),
            'last_checked_at' => $this->monitoring->lastCheckedAt(),
            'resources_tracked' => $this->monitoring->resourcesTracked(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function eventsArray(): array
    {
        return $this->eventsToArray($this->monitoring->events());
    }

    /**
     * @param array<int, MonitoringEvent> $events
     * @return array<int, array<string, mixed>>
     */
    private function eventsToArray(array $events): array
    {
        return array_map(static fn (MonitoringEvent $event): array => $event->toArray(), $events);
    }
}
