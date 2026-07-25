<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use Brain\Monkey\Functions;
use OxyAI\Repositories\UserRepository;
use OxyAI\Tests\Unit\TestCase;
use WP_User;

final class UserRepositoryTest extends TestCase
{
    public function test_find_returns_null_when_user_does_not_exist(): void
    {
        Functions\expect('get_userdata')->once()->with(999)->andReturn(false);

        $repository = new UserRepository();

        self::assertNull($repository->find(999));
    }

    public function test_find_returns_normalized_array_when_user_exists(): void
    {
        Functions\expect('get_userdata')->once()->with(7)->andReturn($this->makeUser());

        $repository = new UserRepository();

        self::assertSame(
            [
                'id'           => 7,
                'login'        => 'jane',
                'email'        => 'jane@example.test',
                'display_name' => 'Jane Doe',
                'roles'        => ['administrator'],
            ],
            $repository->find(7)
        );
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        Functions\expect('get_user_by')
            ->once()
            ->with('email', 'missing@example.test')
            ->andReturn(false);

        $repository = new UserRepository();

        self::assertNull($repository->findByEmail('missing@example.test'));
    }

    public function test_find_by_email_returns_normalized_array_when_found(): void
    {
        Functions\expect('get_user_by')
            ->once()
            ->with('email', 'jane@example.test')
            ->andReturn($this->makeUser());

        $repository = new UserRepository();

        self::assertSame(7, $repository->findByEmail('jane@example.test')['id']);
    }

    public function test_current_user_id_delegates_to_wordpress(): void
    {
        Functions\expect('get_current_user_id')->once()->andReturn(7);

        $repository = new UserRepository();

        self::assertSame(7, $repository->currentUserId());
    }

    public function test_current_user_can_delegates_to_wordpress(): void
    {
        Functions\expect('current_user_can')->once()->with('manage_oxy')->andReturn(true);

        $repository = new UserRepository();

        self::assertTrue($repository->currentUserCan('manage_oxy'));
    }

    public function test_user_can_returns_false_when_wordpress_denies_capability(): void
    {
        Functions\expect('user_can')->once()->with(7, 'manage_oxy')->andReturn(false);

        $repository = new UserRepository();

        self::assertFalse($repository->userCan(7, 'manage_oxy'));
    }

    private function makeUser(): WP_User
    {
        $user = new WP_User();
        $user->ID = 7;
        $user->user_login = 'jane';
        $user->user_email = 'jane@example.test';
        $user->display_name = 'Jane Doe';
        $user->roles = ['administrator'];

        return $user;
    }
}
