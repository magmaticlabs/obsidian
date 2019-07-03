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

    /**
     * @test
     */
    public function key_gen_works()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        $storage->assertExists('obsidian-build.key.pub');
    }

    /**
     * @test
     */
    public function key_already_exists_no_replace()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');
        $storage->put('obsidian-build.key', '__testing__');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->expectsQuestion('Replace Key [y/n]', 'n');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        $this->assertSame('__testing__', $storage->get('obsidian-build.key'));
    }

    /**
     * @test
     */
    public function key_already_exists_replace()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::fake('local');
        $storage->put('obsidian-build.key', '__testing__');

        $cmd = $this->artisan('obsidian:buildkey');
        $cmd->expectsQuestion('Replace Key [y/n]', 'y');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $storage->assertExists('obsidian-build.key');
        $this->assertNotSame('__testing__', $storage->get('obsidian-build.key'));
    }
}
