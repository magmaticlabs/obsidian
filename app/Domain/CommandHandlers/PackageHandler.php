<?php

namespace MagmaticLabs\Obsidian\Domain\CommandHandlers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\CommandHandler;

final class PackageHandler extends CommandHandler
{
    /**
     * Create a new package.
     *
     * @param Command $command
     *
     * @command package.create
     */
    public function handleCreate(Command $command)
    {
        Package::create($command->getData());
    }

    /**
     * Update an existing package.
     *
     * @param Command $command
     *
     * @command package.update
     */
    public function handleUpdate(Command $command)
    {
        $package = Package::find($command->getObjectId());
        $package->update($command->getData('attributes'));
    }

    /**
     * Destroy an existing package.
     *
     * @param Command $command
     *
     * @command package.destroy
     */
    public function handleDestroy(Command $command)
    {
        Package::find($command->getObjectId())->delete();
    }
}
