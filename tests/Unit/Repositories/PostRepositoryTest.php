<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use Brain\Monkey\Functions;
use InvalidArgumentException;
use Mockery;
use OxyAI\Repositories\PostRepository;
use OxyAI\Tests\Unit\TestCase;
use WP_Post;

final class PostRepositoryTest extends TestCase
{
    public function test_find_returns_null_when_post_does_not_exist(): void
    {
        Functions\expect('get_post')->once()->with(999)->andReturn(null);

        $repository = new PostRepository();

        self::assertNull($repository->find(999));
    }

    public function test_find_returns_normalized_array_when_post_exists(): void
    {
        $post = $this->makePost();

        Functions\expect('get_post')->once()->with(42)->andReturn($post);
        Functions\expect('get_permalink')->once()->with($post)->andReturn('https://example.test/hello-world/');

        $repository = new PostRepository();

        $result = $repository->find(42);

        self::assertSame(42, $result['id']);
        self::assertSame('Hello World', $result['title']);
        self::assertSame('hello-world', $result['slug']);
        self::assertSame('https://example.test/hello-world/', $result['permalink']);
    }

    public function test_exists_by_id_returns_false_when_post_missing(): void
    {
        Functions\expect('get_post')->once()->with(999)->andReturn(null);

        $repository = new PostRepository();

        self::assertFalse($repository->existsById(999));
    }

    public function test_query_rejects_unbounded_posts_per_page(): void
    {
        $repository = new PostRepository();

        $this->expectException(InvalidArgumentException::class);

        $repository->query(['posts_per_page' => -1]);
    }

    public function test_query_clamps_posts_per_page_to_the_documented_maximum(): void
    {
        Functions\expect('get_posts')
            ->once()
            ->with(Mockery::on(static fn (array $args): bool => $args['posts_per_page'] === 100))
            ->andReturn([]);

        $repository = new PostRepository();

        // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- intentionally exercising the repository's clamp-to-maximum behavior.
        self::assertSame([], $repository->query(['posts_per_page' => 5000]));
    }

    public function test_query_defaults_posts_per_page_when_not_specified(): void
    {
        Functions\expect('get_posts')
            ->once()
            ->with(Mockery::on(static fn (array $args): bool => $args['posts_per_page'] === 20))
            ->andReturn([]);

        $repository = new PostRepository();

        self::assertSame([], $repository->query([]));
    }

    public function test_count_by_type_returns_zero_when_wp_count_posts_returns_non_object(): void
    {
        Functions\expect('wp_count_posts')->once()->with('page')->andReturn(false);

        $repository = new PostRepository();

        self::assertSame(0, $repository->countByType('page'));
    }

    public function test_count_by_type_returns_publish_count(): void
    {
        $counts = (object) ['publish' => 12, 'draft' => 3];

        Functions\expect('wp_count_posts')->once()->with('post')->andReturn($counts);

        $repository = new PostRepository();

        self::assertSame(12, $repository->countByType('post'));
    }

    private function makePost(): WP_Post
    {
        $post = new WP_Post();
        $post->ID = 42;
        $post->post_title = 'Hello World';
        $post->post_name = 'hello-world';
        $post->post_type = 'post';
        $post->post_status = 'publish';
        $post->post_excerpt = '';
        $post->post_content = 'Body';
        $post->post_date_gmt = '2026-01-01 00:00:00';
        $post->post_modified_gmt = '2026-01-01 00:00:00';

        return $post;
    }
}
