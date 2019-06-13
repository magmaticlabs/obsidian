<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class BuildKeyTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function testKeyGenWorks()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        $storage->assertExists('obsidian-build.key.pub');
    }

    public function testKeyAlreadyExistsNoReplace()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');
        $storage->put('obsidian-build.key', '__testing__');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->expectsQuestion('Replace Key [y/n]', 'n');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        static::assertSame('__testing__', $storage->get('obsidian-build.key'));
    }

    public function testKeyAlreadyExistsReplace()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');
        $storage->put('obsidian-build.key', '__testing__');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->expectsQuestion('Replace Key [y/n]', 'y');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        static::assertNotSame('__testing__', $storage->get('obsidian-build.key'));
    }
}
