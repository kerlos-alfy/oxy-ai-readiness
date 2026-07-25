<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use Brain\Monkey\Functions;
use InvalidArgumentException;
use OxyAI\Repositories\OptionsRepository;
use OxyAI\Tests\Unit\TestCase;

final class OptionsRepositoryTest extends TestCase
{
    public function test_get_returns_value_from_prefixed_option(): void
    {
        Functions\expect('get_option')
            ->once()
            ->with('oxy_ai_example_key', 'fallback')
            ->andReturn('stored-value');

        $repository = new OptionsRepository();

        self::assertSame('stored-value', $repository->get('example_key', 'fallback'));
    }

    public function test_set_stores_value_with_prefixed_key_and_autoload_false_by_default(): void
    {
        Functions\expect('update_option')
            ->once()
            ->with('oxy_ai_example_key', 'value', false)
            ->andReturn(true);

        $repository = new OptionsRepository();

        self::assertTrue($repository->set('example_key', 'value'));
    }

    public function test_set_honors_explicit_autoload_true(): void
    {
        Functions\expect('update_option')
            ->once()
            ->with('oxy_ai_example_key', 'value', true)
            ->andReturn(true);

        $repository = new OptionsRepository();

        self::assertTrue($repository->set('example_key', 'value', true));
    }

    public function test_delete_removes_prefixed_option(): void
    {
        Functions\expect('delete_option')
            ->once()
            ->with('oxy_ai_example_key')
            ->andReturn(true);

        $repository = new OptionsRepository();

        self::assertTrue($repository->delete('example_key'));
    }

    public function test_has_returns_false_when_option_is_missing(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturnUsing(static fn (string $key, mixed $default = false): mixed => $default);

        $repository = new OptionsRepository();

        self::assertFalse($repository->has('missing_key'));
    }

    public function test_has_returns_true_when_option_is_present(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn('stored-value');

        $repository = new OptionsRepository();

        self::assertTrue($repository->has('example_key'));
    }

    public function test_get_rejects_invalid_key(): void
    {
        $repository = new OptionsRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->get('Invalid Key!');
    }

    public function test_get_rejects_empty_key(): void
    {
        $repository = new OptionsRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->get('');
    }
}
