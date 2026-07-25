<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use Brain\Monkey\Functions;
use InvalidArgumentException;
use OxyAI\Repositories\TransientRepository;
use OxyAI\Tests\Unit\TestCase;

final class TransientRepositoryTest extends TestCase
{
    public function test_get_returns_value_from_prefixed_transient(): void
    {
        Functions\expect('get_transient')
            ->once()
            ->with('oxy_ai_example')
            ->andReturn('cached-value');

        $repository = new TransientRepository();

        self::assertSame('cached-value', $repository->get('example'));
    }

    public function test_set_stores_value_with_prefixed_key(): void
    {
        Functions\expect('set_transient')
            ->once()
            ->with('oxy_ai_example', 'value', 300)
            ->andReturn(true);

        $repository = new TransientRepository();

        self::assertTrue($repository->set('example', 'value', 300));
    }

    public function test_set_rejects_negative_ttl(): void
    {
        $repository = new TransientRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->set('example', 'value', -1);
    }

    public function test_delete_removes_prefixed_transient(): void
    {
        Functions\expect('delete_transient')
            ->once()
            ->with('oxy_ai_example')
            ->andReturn(true);

        $repository = new TransientRepository();

        self::assertTrue($repository->delete('example'));
    }

    public function test_remember_returns_cached_value_without_invoking_callback_on_hit(): void
    {
        Functions\expect('get_transient')
            ->once()
            ->with('oxy_ai_example')
            ->andReturn('cached-value');

        $repository = new TransientRepository();

        $result = $repository->remember('example', 300, function (): string {
            self::fail('Callback must not be invoked on a cache hit.');
        });

        self::assertSame('cached-value', $result);
    }

    public function test_remember_computes_and_stores_value_on_cache_miss(): void
    {
        Functions\expect('get_transient')->once()->andReturn(false);
        Functions\expect('set_transient')
            ->once()
            ->with('oxy_ai_example', 'computed-value', 300)
            ->andReturn(true);

        $repository = new TransientRepository();

        $result = $repository->remember('example', 300, static fn (): string => 'computed-value');

        self::assertSame('computed-value', $result);
    }

    public function test_key_exceeding_max_length_is_rejected(): void
    {
        $repository = new TransientRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->get(str_repeat('a', 150));
    }

    public function test_invalid_key_format_is_rejected(): void
    {
        $repository = new TransientRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->get('Invalid Key!');
    }
}
