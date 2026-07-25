<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Repositories;

use InvalidArgumentException;
use Mockery;
use OxyAI\Repositories\FileRepository;
use OxyAI\Tests\Unit\TestCase;
use WP_Filesystem_Base;

final class FileRepositoryTest extends TestCase
{
    public function test_read_returns_null_when_file_does_not_exist(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->with('/base/reports/missing.json')->andReturn(false);

        $repository = new FileRepository('/base', $fs);

        self::assertNull($repository->read('reports/missing.json'));
    }

    public function test_read_returns_contents_when_file_exists_and_is_readable(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->with('/base/reports/report.json')->andReturn(true);
        $fs->shouldReceive('is_readable')->once()->with('/base/reports/report.json')->andReturn(true);
        $fs->shouldReceive('get_contents')->once()->with('/base/reports/report.json')->andReturn('{"ok":true}');

        $repository = new FileRepository('/base', $fs);

        self::assertSame('{"ok":true}', $repository->read('reports/report.json'));
    }

    public function test_read_returns_null_when_file_exists_but_is_not_readable(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->andReturn(true);
        $fs->shouldReceive('is_readable')->once()->andReturn(false);

        $repository = new FileRepository('/base', $fs);

        self::assertNull($repository->read('reports/report.json'));
    }

    public function test_write_rejects_path_traversal(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);

        $repository = new FileRepository('/base', $fs);

        $this->expectException(InvalidArgumentException::class);

        $repository->write('../../etc/passwd', 'malicious');
    }

    public function test_write_rejects_absolute_paths(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);

        $repository = new FileRepository('/base', $fs);

        $this->expectException(InvalidArgumentException::class);

        $repository->write('/etc/passwd', 'malicious');
    }

    public function test_write_rejects_null_byte_in_path(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);

        $repository = new FileRepository('/base', $fs);

        $this->expectException(InvalidArgumentException::class);

        $repository->write("reports/report.json\0.php", 'malicious');
    }

    public function test_write_creates_directory_writes_temp_file_then_moves_it_atomically(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('is_dir')->once()->with('/base/reports')->andReturn(false);
        $fs->shouldReceive('mkdir')->once()->with('/base/reports', 0755)->andReturn(true);
        $fs->shouldReceive('put_contents')
            ->once()
            ->with(
                Mockery::on(static fn (string $path): bool => str_starts_with($path, '/base/reports/report.json.tmp-')),
                'contents',
                0644
            )
            ->andReturn(true);
        $fs->shouldReceive('move')
            ->once()
            ->with(
                Mockery::on(static fn (string $path): bool => str_starts_with($path, '/base/reports/report.json.tmp-')),
                '/base/reports/report.json',
                true
            )
            ->andReturn(true);

        $repository = new FileRepository('/base', $fs);

        self::assertTrue($repository->write('reports/report.json', 'contents'));
    }

    public function test_write_cleans_up_temp_file_when_move_fails(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('is_dir')->once()->andReturn(true);
        $fs->shouldReceive('put_contents')->once()->andReturn(true);
        $fs->shouldReceive('move')->once()->andReturn(false);
        $fs->shouldReceive('delete')->once()->andReturn(true);

        $repository = new FileRepository('/base', $fs);

        self::assertFalse($repository->write('reports/report.json', 'contents'));
    }

    public function test_write_returns_false_when_directory_cannot_be_created(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('is_dir')->once()->andReturn(false);
        $fs->shouldReceive('mkdir')->once()->andReturn(false);

        $repository = new FileRepository('/base', $fs);

        self::assertFalse($repository->write('reports/report.json', 'contents'));
    }

    public function test_checksum_returns_sha256_of_contents(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->andReturn(true);
        $fs->shouldReceive('is_readable')->once()->andReturn(true);
        $fs->shouldReceive('get_contents')->once()->andReturn('contents');

        $repository = new FileRepository('/base', $fs);

        self::assertSame(hash('sha256', 'contents'), $repository->checksum('reports/report.json'));
    }

    public function test_checksum_returns_null_when_file_missing(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->andReturn(false);

        $repository = new FileRepository('/base', $fs);

        self::assertNull($repository->checksum('reports/missing.json'));
    }

    public function test_delete_returns_true_when_file_already_absent(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->andReturn(false);

        $repository = new FileRepository('/base', $fs);

        self::assertTrue($repository->delete('reports/missing.json'));
    }

    public function test_delete_delegates_to_filesystem_when_file_present(): void
    {
        $fs = Mockery::mock(WP_Filesystem_Base::class);
        $fs->shouldReceive('exists')->once()->andReturn(true);
        $fs->shouldReceive('delete')->once()->with('/base/reports/report.json')->andReturn(true);

        $repository = new FileRepository('/base', $fs);

        self::assertTrue($repository->delete('reports/report.json'));
    }
}
