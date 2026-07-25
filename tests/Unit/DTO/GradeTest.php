<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\DTO;

use OxyAI\DTO\Grade;
use OxyAI\Tests\Unit\TestCase;

final class GradeTest extends TestCase
{
    /**
     * @return iterable<string, array{0: float, 1: Grade, 2: string}>
     */
    public static function scoreProvider(): iterable
    {
        yield '100 is A+ Excellent' => [100.0, Grade::APlus, 'Excellent'];
        yield '98 is A+ Excellent (lower boundary)' => [98.0, Grade::APlus, 'Excellent'];
        yield '97.99 is A Excellent (just under A+)' => [97.99, Grade::A, 'Excellent'];
        yield '95 is A Excellent (lower boundary)' => [95.0, Grade::A, 'Excellent'];
        yield '94.99 is A- Excellent (just under A)' => [94.99, Grade::AMinus, 'Excellent'];
        yield '90 is A- Excellent (lower boundary)' => [90.0, Grade::AMinus, 'Excellent'];
        yield '89.99 is B+ Advanced (just under A-)' => [89.99, Grade::BPlus, 'Advanced'];
        yield '85 is B+ Advanced (lower boundary)' => [85.0, Grade::BPlus, 'Advanced'];
        yield '84.99 is B Advanced (just under B+)' => [84.99, Grade::B, 'Advanced'];
        yield '80 is B Advanced (lower boundary)' => [80.0, Grade::B, 'Advanced'];
        yield '79.99 is B- Advanced (just under B)' => [79.99, Grade::BMinus, 'Advanced'];
        yield '75 is B- Advanced (lower boundary)' => [75.0, Grade::BMinus, 'Advanced'];
        yield '74.99 is C+ Good (just under B-)' => [74.99, Grade::CPlus, 'Good'];
        yield '70 is C+ Good (lower boundary)' => [70.0, Grade::CPlus, 'Good'];
        yield '69.99 is C Good (just under C+)' => [69.99, Grade::C, 'Good'];
        yield '60 is C Good (lower boundary)' => [60.0, Grade::C, 'Good'];
        yield '59.99 is D Basic (just under C)' => [59.99, Grade::D, 'Basic'];
        yield '40 is D Basic (lower boundary)' => [40.0, Grade::D, 'Basic'];
        yield '39.99 is F Poor (just under D)' => [39.99, Grade::F, 'Poor'];
        yield '0 is F Poor' => [0.0, Grade::F, 'Poor'];
    }

    /**
     * @dataProvider scoreProvider
     */
    public function test_from_score_resolves_the_canonical_grade_and_label(
        float $score,
        Grade $expectedGrade,
        string $expectedLabel
    ): void {
        $grade = Grade::fromScore($score);

        self::assertSame($expectedGrade, $grade);
        self::assertSame($expectedLabel, $grade->label());
    }
}
