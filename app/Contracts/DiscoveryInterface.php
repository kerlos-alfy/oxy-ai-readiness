<?php

/**
 * Contract for a Module's Discovery provider.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Contracts;

use OxyAI\DTO\DiscoveredResource;

/**
 * Per docs/22-Plugin-SDK.md's SDK Interfaces list. A Module that has
 * something discoverable (a file, endpoint, header, etc.) implements
 * this and registers itself with `DiscoveryService`; the Discovery
 * Engine calls discover() on every registered provider to build the
 * Discovery Map. Placed in `app/Contracts/` alongside
 * `ModuleInterface`/`StandardInterface` rather than a `Standards`-style
 * subfolder — `docs/04-Folder-Structure.md`'s Contracts/ list doesn't
 * separately enumerate SDK interfaces by folder.
 */
interface DiscoveryInterface
{
    /**
     * @return array<int, DiscoveredResource>
     */
    public function discover(): array;
}
