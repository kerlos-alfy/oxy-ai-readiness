<?php

/**
 * Central Generation Engine: generate/preview/publish/rollback pipeline.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\Contracts\GeneratorInterface;
use OxyAI\DTO\GenerationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\GenerationException;
use OxyAI\Exceptions\ModuleException;
use OxyAI\Repositories\FileRepository;

/**
 * Per docs/17-Generation-Engine.md: one centralized framework instead
 * of every module writing its own publish/rollback/versioning logic.
 * `publish()` implements the Phase 6 exit criterion directly: it runs
 * the generator's associated Discovery Map entry through
 * `ValidationService` first and refuses to write anything if any
 * result is FAIL, then keeps the previous file content as a single
 * rollback point before overwriting.
 *
 * Versioning is a simple two-slot scheme (current + one previous), not
 * full history — sufficient for "rollback restores prior version"
 * (singular) without the added complexity of a full version store,
 * which no `oxy_*` table exists yet to back anyway.
 */
final class GenerationService
{
    /** @var array<string, GeneratorInterface> */
    private array $generators = [];

    /** @var array<string, string> */
    private array $cache = [];

    /** @var array<string, int> */
    private array $versions = [];

    public function __construct(
        private readonly ValidationService $validation,
        private readonly DiscoveryService $discovery,
        private readonly FileRepository $files
    ) {
    }

    public function registerGenerator(string $id, GeneratorInterface $generator): void
    {
        if (isset($this->generators[$id])) {
            throw new ModuleException(sprintf('Generator "%s" is already registered.', $id));
        }

        $this->generators[$id] = $generator;
    }

    public function has(string $id): bool
    {
        return isset($this->generators[$id]);
    }

    public function generate(string $id): string
    {
        $generator = $this->get($id);

        do_action('oxy_ai_generation_started', $id);

        $output = $generator->generate();
        $this->cache[$id] = $output;

        do_action('oxy_ai_generation_completed', $id, $output);

        return $output;
    }

    public function preview(string $id): string
    {
        return $this->generate($id);
    }

    public function cached(string $id): ?string
    {
        return $this->cache[$id] ?? null;
    }

    /**
     * The actually-published content on disk for a generator, or null
     * if it has never been published. Distinct from cached(), which
     * reflects the last generate()/preview() call and may not have
     * been published yet.
     */
    public function currentContent(string $id): ?string
    {
        $this->get($id);

        return $this->files->read($this->pathFor($id));
    }

    public function version(string $id): int
    {
        return $this->versions[$id] ?? 0;
    }

    /**
     * The Discovery Map entry id a registered generator's output
     * corresponds to — lets a caller (e.g. `AutoFixService`) re-check
     * that resource after publishing without needing its own separate
     * generator-to-resource lookup.
     */
    public function resourceIdFor(string $id): string
    {
        return $this->get($id)->resourceId();
    }

    /**
     * Validates the generator's Discovery Map entry, generates its
     * output, backs up any existing content, and writes the new
     * content — in that order, so a validation or write failure never
     * touches storage.
     */
    public function publish(string $id): GenerationResult
    {
        $generator = $this->get($id);
        $resourceId = $generator->resourceId();
        $map = $this->discovery->map();

        if (!isset($map[$resourceId])) {
            throw new GenerationException(
                sprintf('Cannot publish "%s": resource "%s" has not been discovered.', $id, $resourceId)
            );
        }

        foreach ($this->validation->validate($map[$resourceId]) as $result) {
            if ($result->status === ValidationStatus::Fail) {
                do_action('oxy_ai_generation_failed', $id, $result);

                throw new GenerationException(
                    sprintf('Cannot publish "%s": validation failed (%s).', $id, $result->message)
                );
            }
        }

        $output = $this->generate($id);
        $path = $this->pathFor($id);

        $existing = $this->files->read($path);

        if ($existing !== null) {
            $this->files->write($this->previousPathFor($id), $existing);
        }

        if (!$this->files->write($path, $output)) {
            throw new GenerationException(sprintf('Failed to write generated output for "%s".', $id));
        }

        $this->versions[$id] = $this->version($id) + 1;

        $result = new GenerationResult(
            generatorId: $id,
            path: $path,
            version: $this->versions[$id],
            checksum: (string) $this->files->checksum($path),
            publishedAt: gmdate('c')
        );

        do_action('oxy_ai_generation_published', $result);

        return $result;
    }

    public function rollback(string $id): void
    {
        $this->get($id);

        $previousPath = $this->previousPathFor($id);
        $previous = $this->files->read($previousPath);

        if ($previous === null) {
            throw new GenerationException(sprintf('Cannot roll back "%s": no prior version exists.', $id));
        }

        if (!$this->files->write($this->pathFor($id), $previous)) {
            throw new GenerationException(sprintf('Failed to restore the prior version for "%s".', $id));
        }

        $this->files->delete($previousPath);

        $this->versions[$id] = max(0, $this->version($id) - 1);

        do_action('oxy_ai_generation_rolled_back', $id);
    }

    private function pathFor(string $id): string
    {
        return $id . '.txt';
    }

    private function previousPathFor(string $id): string
    {
        return $id . '.previous.txt';
    }

    private function get(string $id): GeneratorInterface
    {
        if (!isset($this->generators[$id])) {
            throw new ModuleException(sprintf('Generator "%s" is not registered.', $id));
        }

        return $this->generators[$id];
    }
}
