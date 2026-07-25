<?php

/**
 * Central Validation Engine: runs registered validators.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\Contracts\ValidatorInterface;
use OxyAI\DTO\DiscoveredResource;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;
use OxyAI\Exceptions\ModuleException;

/**
 * Per docs/16-Validation-Engine.md: one centralized validation
 * framework instead of every module validating its own resources.
 * Runs every registered validator against a given Discovery Map entry
 * and returns their results — deterministic, since each validator is a
 * pure function of the resource it's given.
 */
final class ValidationService
{
    /** @var array<string, ValidatorInterface> */
    private array $validators = [];

    public function registerValidator(string $id, ValidatorInterface $validator): void
    {
        if (isset($this->validators[$id])) {
            throw new ModuleException(sprintf('Validator "%s" is already registered.', $id));
        }

        $this->validators[$id] = $validator;
    }

    public function has(string $id): bool
    {
        return isset($this->validators[$id]);
    }

    public function count(): int
    {
        return count($this->validators);
    }

    /**
     * @return array<int, ValidationResult>
     */
    public function validate(DiscoveredResource $resource): array
    {
        do_action('oxy_ai_validation_started', $resource);

        $results = [];

        foreach ($this->validators as $validator) {
            $result = $validator->validate($resource);
            $results[] = $result;

            $this->fireResultEvent($result);
        }

        do_action('oxy_ai_validation_completed', $resource, $results);

        return $results;
    }

    private function fireResultEvent(ValidationResult $result): void
    {
        match ($result->status) {
            ValidationStatus::Pass => do_action('oxy_ai_validation_passed', $result),
            ValidationStatus::Fail => do_action('oxy_ai_validation_failed', $result),
            ValidationStatus::Warning => do_action('oxy_ai_validation_warning', $result),
            ValidationStatus::Info, ValidationStatus::Skipped, ValidationStatus::Unknown => null,
        };
    }
}
