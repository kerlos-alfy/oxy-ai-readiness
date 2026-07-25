<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Core;

use Brain\Monkey\Functions;
use OxyAI\Core\Hooks;
use OxyAI\Tests\Unit\TestCase;

final class HooksTest extends TestCase
{
    public function test_add_action_registers_with_wordpress_and_tracks_it(): void
    {
        $callback = static function (): void {
        };

        Functions\expect('add_action')
            ->once()
            ->with('plugins_loaded', $callback, 20, 2);

        $hooks = new Hooks();
        $hooks->addAction('plugins_loaded', $callback, 20, 2);

        self::assertSame(
            [['hook' => 'plugins_loaded', 'callback' => $callback, 'priority' => 20, 'args' => 2]],
            $hooks->registeredActions()
        );
        self::assertSame([], $hooks->registeredFilters());
    }

    public function test_add_filter_registers_with_wordpress_and_tracks_it_with_default_priority_and_args(): void
    {
        $callback = static fn (mixed $value): mixed => $value;

        Functions\expect('add_filter')
            ->once()
            ->with('the_content', $callback, 10, 1);

        $hooks = new Hooks();
        $hooks->addFilter('the_content', $callback);

        self::assertSame(
            [['hook' => 'the_content', 'callback' => $callback, 'priority' => 10, 'args' => 1]],
            $hooks->registeredFilters()
        );
        self::assertSame([], $hooks->registeredActions());
    }
}
