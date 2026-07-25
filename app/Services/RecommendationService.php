<?php

/**
 * Central Recommendation Engine: turns issues into actionable advice.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Services;

use OxyAI\DTO\Recommendation;
use OxyAI\DTO\ValidationResult;
use OxyAI\DTO\ValidationStatus;

/**
 * Per docs/19-Recommendation-Engine.md's pipeline (Audit Results →
 * Issue Analysis → Priority Calculation → ... → Recommendations):
 * takes the `ValidationResult`s an audit/validation pass already
 * produced and turns each FAIL/WARNING into a `Recommendation`. PASS/
 * INFO/SKIPPED/UNKNOWN results aren't issues, so they produce no
 * recommendation. `autoFixAvailable` is genuinely checked against
 * `GenerationService` — a result's `validator` field is the same id a
 * module registers itself under in both `ValidationService` and
 * `GenerationService`, so this is a real capability check, not a
 * guess.
 */
final class RecommendationService
{
    public function __construct(private readonly GenerationService $generation)
    {
    }

    /**
     * @param array<int, ValidationResult> $results
     * @return array<int, Recommendation>
     */
    public function generate(array $results): array
    {
        $recommendations = [];

        foreach ($results as $result) {
            if ($result->status !== ValidationStatus::Fail && $result->status !== ValidationStatus::Warning) {
                continue;
            }

            $recommendations[] = new Recommendation(
                id: sprintf('%s:%s', $result->resourceId, $result->validator),
                title: sprintf('Fix %s', $result->resourceId),
                description: $result->message,
                category: $result->validator,
                priority: $result->status === ValidationStatus::Fail ? 'critical' : 'medium',
                autoFixAvailable: $this->generation->has($result->validator)
            );
        }

        do_action('oxy_ai_recommendations_generated', $recommendations);

        return $recommendations;
    }
}
