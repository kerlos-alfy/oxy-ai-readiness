<?php

/**
 * Minimal local stand-ins for WordPress core classes, used only under
 * PHPUnit + Brain Monkey (which mocks WordPress *functions*, not
 * classes).
 *
 * These are intentionally NOT a substitute for a real WordPress test
 * environment or for `php-stubs/wordpress-stubs` (added as a dev
 * dependency for PHPStan; PHPStan analyses `app/`, never this file).
 * They exist only so unit tests can construct plain WP_User/WP_Post/
 * WP_REST_Request/WP_REST_Response value objects and mock
 * WP_Filesystem_Base's public surface, without loading WordPress
 * itself.
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

if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /** @var array<string, mixed> */
        private array $params = [];

        public function get_param(string $key): mixed
        {
            return $this->params[$key] ?? null;
        }

        public function set_param(string $key, mixed $value): void
        {
            $this->params[$key] = $value;
        }
    }
}

if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        public function __construct(private mixed $data = null, private int $status = 200)
        {
        }

        public function get_data(): mixed
        {
            return $this->data;
        }

        public function get_status(): int
        {
            return $this->status;
        }
    }
}
