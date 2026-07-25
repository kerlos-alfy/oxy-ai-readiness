<?php

/**
 * Base test case wiring Brain Monkey for every unit test.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Tests\Unit;

use Brain\Monkey;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Per docs/28-Testing-Strategy.md's Unit Test Requirements ("No WordPress
 * Bootstrap Where Possible", "Mock External Dependencies", "Fast
 * Execution") and its TOOLS list (Brain Monkey, Mockery), every unit test
 * mocks WordPress core functions instead of bootstrapping WordPress.
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();

        parent::tearDown();
    }
}
