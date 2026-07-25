<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use OxyAI\Contracts\RepositoryInterface;
use OxyAI\Repositories\FileRepository;
use OxyAI\Repositories\OptionsRepository;
use OxyAI\Repositories\PostRepository;
use OxyAI\Repositories\TransientRepository;
use OxyAI\Repositories\UserRepository;
use OxyAI\Tests\Unit\TestCase;

final class RepositoryContractTest extends TestCase
{
    /**
     * @return array<string,array{0:class-string}>
     */
    public static function repositoryClassProvider(): array
    {
        return [
            'OptionsRepository'   => [OptionsRepository::class],
            'TransientRepository' => [TransientRepository::class],
            'UserRepository'      => [UserRepository::class],
            'PostRepository'      => [PostRepository::class],
            'FileRepository'      => [FileRepository::class],
        ];
    }

    /**
     * @dataProvider repositoryClassProvider
     *
     * @param class-string $repositoryClass
     */
    public function test_foundation_repository_implements_repository_interface(string $repositoryClass): void
    {
        self::assertContains(
            RepositoryInterface::class,
            class_implements($repositoryClass) ?: [],
            sprintf(
                '%s must implement RepositoryInterface (docs/02-Architecture.md Repository Pattern).',
                $repositoryClass
            )
        );
    }

    /**
     * @dataProvider repositoryClassProvider
     *
     * @param class-string $repositoryClass
     */
    public function test_foundation_repository_is_final(string $repositoryClass): void
    {
        $reflection = new \ReflectionClass($repositoryClass);

        self::assertTrue(
            $reflection->isFinal(),
            sprintf('%s must be final per docs/29-Developer-Guide.md coding standards.', $repositoryClass)
        );
    }
}
