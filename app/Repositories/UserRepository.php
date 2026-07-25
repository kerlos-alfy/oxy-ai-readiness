<?php

/**
 * Repository wrapping WordPress user lookup and capability checks.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Repositories;

use OxyAI\Contracts\RepositoryInterface;
use WP_User;

/**
 * Wraps get_userdata()/get_user_by()/current_user_can()/user_can().
 *
 * Normalizes WP_User objects into plain arrays so that Application/Domain
 * layer code never has to depend on the WP_User type directly, per
 * docs/02-Architecture.md's Domain Layer principle ("No WordPress code
 * should exist here whenever possible"). Capability *names* used by this
 * repository's callers (e.g. `manage_oxy`, `view_audit`) are defined and
 * registered by the Security module in a later phase — this repository
 * only provides the generic lookup/check primitives.
 */
final class UserRepository implements RepositoryInterface
{
    /**
     * @return array{id:int,login:string,email:string,display_name:string,roles:array<int,string>}|null
     */
    public function find(int $userId): ?array
    {
        $user = get_userdata($userId);

        return $user instanceof WP_User ? $this->normalize($user) : null;
    }

    /**
     * @return array{id:int,login:string,email:string,display_name:string,roles:array<int,string>}|null
     */
    public function findByEmail(string $email): ?array
    {
        $user = get_user_by('email', $email);

        return $user instanceof WP_User ? $this->normalize($user) : null;
    }

    public function currentUserId(): int
    {
        return get_current_user_id();
    }

    public function userCan(int $userId, string $capability): bool
    {
        return user_can($userId, $capability);
    }

    public function currentUserCan(string $capability): bool
    {
        return current_user_can($capability);
    }

    /**
     * @return array{id:int,login:string,email:string,display_name:string,roles:array<int,string>}
     */
    private function normalize(WP_User $user): array
    {
        return [
            'id'           => (int) $user->ID,
            'login'        => (string) $user->user_login,
            'email'        => (string) $user->user_email,
            'display_name' => (string) $user->display_name,
            'roles'        => array_values($user->roles),
        ];
    }
}
