<?php

/**
 * Central Auto Fix Engine: backup, execute, verify, rollback.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\FixResult;
use OxyAI\DTO\FixTier;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\GenerationException;

/**
 * Per docs/18-AutoFix-Engine.md's Fix Pipeline (Backup → Execute →
 * Validate → Verify → Update Score → Complete): reuses
 * `GenerationService`'s existing backup-then-write pipeline (Phase 6 —
 * every `publish()` call already backs up the current content to a
 * `.previous` file before overwriting, and already refuses to write if
 * the resource fails validation) as the "Backup"/"Execute"/"Validate"
 * steps, then adds its own explicit post-fix "Verify" step (re-running
 * validation against the resource *after* the write) before declaring
 * success — matching docs' distinct Execute → Validate → Verify
 * pipeline stages rather than treating one validation pass as both.
 *
 * `FixTier::Safe` runs immediately; `Confirmation`/`Developer` require
 * an explicit `$confirmed = true` argument, modeling "requires
 * confirmation before running" honestly rather than pretending a real
 * confirmation UI exists (Admin UI is a later phase).
 */
final class AutoFixService
{
    private ?FixResult $lastResult = null;

    public function __construct(
        private readonly GenerationService $generation,
        private readonly ValidationService $validation,
        private readonly DiscoveryService $discovery
    ) {
    }

    public function has(string $generatorId): bool
    {
        return $this->generation->has($generatorId);
    }

    public function lastResult(): ?FixResult
    {
        return $this->lastResult;
    }

    public function fix(string $generatorId, FixTier $tier = FixTier::Safe, bool $confirmed = false): FixResult
    {
        if ($tier !== FixTier::Safe && !$confirmed) {
            return $this->remember(new FixResult(
                $generatorId,
                success: false,
                version: $this->generation->version($generatorId),
                message: 'Confirmation required before applying this fix.',
                pending: true
            ));
        }

        do_action('oxy_ai_autofix_started', $generatorId);

        try {
            $publishResult = $this->generation->publish($generatorId);
        } catch (GenerationException $exception) {
            do_action('oxy_ai_autofix_failed', $generatorId, $exception);

            return $this->remember(new FixResult(
                $generatorId,
                success: false,
                version: $this->generation->version($generatorId),
                message: $exception->getMessage()
            ));
        }

        if ($this->verify($generatorId)) {
            do_action('oxy_ai_autofix_completed', $generatorId);

            return $this->remember(new FixResult(
                $generatorId,
                success: true,
                version: $publishResult->version,
                message: 'Fix applied and verified successfully.'
            ));
        }

        $this->generation->rollback($generatorId);

        do_action('oxy_ai_autofix_rolled_back', $generatorId);

        return $this->remember(new FixResult(
            $generatorId,
            success: false,
            version: $this->generation->version($generatorId),
            message: 'Verification failed after the fix; rolled back to the prior version.'
        ));
    }

    private function remember(FixResult $result): FixResult
    {
        $this->lastResult = $result;

        return $result;
    }

    public function rollback(string $generatorId): void
    {
        $this->generation->rollback($generatorId);

        do_action('oxy_ai_autofix_rollback_completed', $generatorId);
    }

    private function verify(string $generatorId): bool
    {
        $resourceId = $this->generation->resourceIdFor($generatorId);
        $map = $this->discovery->map();

        if (!isset($map[$resourceId])) {
            return false;
        }

        foreach ($this->validation->validate($map[$resourceId]) as $result) {
            if ($result->status === ValidationStatus::Fail) {
                return false;
            }
        }

        return true;
    }
}
