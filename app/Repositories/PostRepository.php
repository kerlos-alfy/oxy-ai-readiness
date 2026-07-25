<?php

/**
 * Repository wrapping WordPress post/page/CPT read access.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\Repositories;

use InvalidArgumentException;
use OxyAI\Contracts\RepositoryInterface;
use WP_Post;

/**
 * Wraps get_post()/get_posts() for read access to posts, pages, products
 * and other content types that later modules (Markdown, LLMS, Content
 * Signals, Audit) need to read.
 *
 * This repository is read-focused: Oxy AI Readiness reads and analyzes
 * content, it does not author it. Every query is defensively bounded per
 * docs/27-Performance-Spec.md's "Avoid unbounded queries" /
 * "Maximum Rows Per Request 100 default" rules — callers can never
 * accidentally request an unbounded result set through this repository.
 */
final class PostRepository implements RepositoryInterface
{
    private const MAX_PER_PAGE = 100;
    private const DEFAULT_PER_PAGE = 20;

    /**
     * @return array{id:int,title:string,slug:string,type:string,status:string,permalink:string,excerpt:string,content:string,date:string,modified:string}|null
     */
    public function find(int $postId): ?array
    {
        $post = get_post($postId);

        return $post instanceof WP_Post ? $this->normalize($post) : null;
    }

    public function existsById(int $postId): bool
    {
        return get_post($postId) instanceof WP_Post;
    }

    /**
     * @param array<string,mixed> $args WP_Query-style arguments. `posts_per_page`
     *                                  is clamped to self::MAX_PER_PAGE and a
     *                                  value of -1 (unbounded) is rejected.
     *
     * @return array<int,array{id:int,title:string,slug:string,type:string,status:string,permalink:string,excerpt:string,content:string,date:string,modified:string}>
     */
    public function query(array $args): array
    {
        $args = $this->boundArgs($args);

        /** @var WP_Post[] $posts */
        $posts = get_posts($args);

        return array_map(fn (WP_Post $post): array => $this->normalize($post), $posts);
    }

    public function countByType(string $postType): int
    {
        $counts = wp_count_posts($postType);

        if (!is_object($counts)) {
            return 0;
        }

        return (int) ($counts->publish ?? 0);
    }

    /**
     * @param array<string,mixed> $args
     *
     * @return array<string,mixed>
     */
    private function boundArgs(array $args): array
    {
        $requested = $args['posts_per_page'] ?? self::DEFAULT_PER_PAGE;

        if (!is_int($requested) || $requested < 1) {
            throw new InvalidArgumentException(
                'posts_per_page must be a positive integer; unbounded queries (-1) are not permitted.'
            );
        }

        $args['posts_per_page'] = min($requested, self::MAX_PER_PAGE);
        $args['no_found_rows'] = true;
        $args['update_post_meta_cache'] = $args['update_post_meta_cache'] ?? false;
        $args['update_post_term_cache'] = $args['update_post_term_cache'] ?? false;

        return $args;
    }

    /**
     * @return array{id:int,title:string,slug:string,type:string,status:string,permalink:string,excerpt:string,content:string,date:string,modified:string}
     */
    private function normalize(WP_Post $post): array
    {
        return [
            'id'        => (int) $post->ID,
            'title'     => (string) $post->post_title,
            'slug'      => (string) $post->post_name,
            'type'      => (string) $post->post_type,
            'status'    => (string) $post->post_status,
            'permalink' => (string) get_permalink($post),
            'excerpt'   => (string) $post->post_excerpt,
            'content'   => (string) $post->post_content,
            'date'      => (string) $post->post_date_gmt,
            'modified'  => (string) $post->post_modified_gmt,
        ];
    }
}
