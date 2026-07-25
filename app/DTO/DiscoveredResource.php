<?php

/**
 * A single entry in the Discovery Map.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Fields per docs/14-Discovery-Engine.md's "Discovery Map" section: ID,
 * Type, Location, Status, Version, Module, Health, Dependencies,
 * Source, Last Checked. Immutable value object — DiscoveryService
 * never mutates a resource once discovered, only replaces it wholesale
 * on the next scan().
 */
final class DiscoveredResource
{
    /**
     * @param array<int, string> $dependencies
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $location,
        public readonly string $status,
        public readonly string $version,
        public readonly string $module,
        public readonly string $health,
        public readonly array $dependencies,
        public readonly string $source,
        public readonly string $lastChecked
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     type: string,
     *     location: string,
     *     status: string,
     *     version: string,
     *     module: string,
     *     health: string,
     *     dependencies: array<int, string>,
     *     source: string,
     *     last_checked: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'location' => $this->location,
            'status' => $this->status,
            'version' => $this->version,
            'module' => $this->module,
            'health' => $this->health,
            'dependencies' => $this->dependencies,
            'source' => $this->source,
            'last_checked' => $this->lastChecked,
        ];
    }
}
