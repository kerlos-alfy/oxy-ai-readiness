<?php

/**
 * Minimal local stand-ins for WordPress core classes referenced by the
 * Repository Foundation, used only under PHPUnit + Brain Monkey (which
 * mocks WordpPress *functions*, not classes).
 *
 * These are intentionally NOT a substitute for a real WordPress test
 * environment or for `php-stubs/wordpress-stubs` (which should be added
 * as a dev dependency for IDE/PHPStan purposes once `composer install`
 * can actually run — see the Phase 1 report's Environment Limitation
 * section). They exist only so unit tests can construct plain WP_User /
 * WP_Post value objects and mock WP_Filesystem_Base's public surface,
 * without loading WordPress itself.
 *
 * @package OxyAI
 */

declare(strict_types=1);

if (!class_exists('WP_User')) {
    class WP_User
    {
        public int $ID = 0;
        public string $user_login = '';
        public string $user_email = '';
        public string $display_name = '';

        /** @var array<int,string> */
        public array $roles = [];
    }
}

if (!class_exists('WP_Post')) {
    class WP_Post
    {
        public int $ID = 0;
        public string $post_title = '';
        public string $post_name = '';
        public string $post_type = '';
        public string $post_status = '';
        public string $post_excerpt = '';
        public string $post_content = '';
        public string $post_date_gmt = '';
        public string $post_modified_gmt = '';
    }
}

if (!class_exists('WP_Filesystem_Base')) {
    abstract class WP_Filesystem_Base
    {
        abstract public function exists(string $file): bool;

        abstract public function is_readable(string $file): bool;

        abstract public function is_writable(string $file): bool;

        abstract public function is_dir(string $path): bool;

        /**
         * @return string|false
         */
        abstract public function get_contents(string $file);

        /**
         * @param int|false $mode
         */
        abstract public function put_contents(string $file, string $contents, $mode = false): bool;

        abstract public function delete(string $file, bool $recursive = false, ?string $type = null): bool;

        /**
         * @param int|false $chmod
         * @param int|false $chown
         * @param int|false $chgrp
         */
        abstract public function mkdir(string $path, $chmod = false, $chown = false, $chgrp = false): bool;

        abstract public function move(string $source, string $destination, bool $overwrite = false): bool;
    }
}

if (!function_exists('WP_Filesystem')) {
    function WP_Filesystem(): bool
    {
        return true;
    }
}
