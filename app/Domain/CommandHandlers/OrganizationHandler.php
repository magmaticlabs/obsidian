<?php

namespace MagmaticLabs\Obsidian\Domain\CommandHandlers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\CommandHandler;

final class OrganizationHandler extends CommandHandler
{
    /**
     * Create a new organization.
     *
     * @param Command $command
     *
     * @command organization.create
     */
    public function handleCreate(Command $command)
    {
        $organization = Organization::create($command->getData());
        $organization->addMember($user = User::find($command->getUserId()));
        $organization->promoteMember($user);
    }

    /**
     * Update an existing organization.
     *
     * @param Command $command
     *
     * @command organization.update
     */
    public function handleUpdate(Command $command)
    {
        $organization = Organization::find($command->getObjectId());
        $organization->update($command->getData('attributes'));
    }

    /**
     * Destroy an existing organization.
     *
     * @param Command $command
     *
     * @command organization.destroy
     */
    public function handleDestroy(Command $command)
    {
        Organization::find($command->getObjectId())->delete();
    }

    /**
     * Add a new member to an organization.
     *
     * @param Command $command
     *
     * @command organization.members.create
     */
    public function handleAddMember(Command $command)
    {
        $organization = Organization::find($command->getObjectId());

        foreach ($command->getData('users') as $userid) {
            $organization->addMember(User::find($userid));
        }
    }

    /**
     * Remove a member from an organization.
     *
     * @param Command $command
     *
     * @command organization.members.destroy
     */
    public function handleRemoveMember(Command $command)
    {
        $organization = Organization::find($command->getObjectId());

        foreach ($command->getData('users') as $userid) {
            $organization->removeMember(User::find($userid));
        }
    }

    /**
     * Promote a member of an organization to owner.
     *
     * @param Command $command
     *
     * @command organization.owners.create
     */
    public function handlePromoteMember(Command $command)
    {
        $organization = Organization::find($command->getObjectId());

        foreach ($command->getData('users') as $userid) {
            $organization->promoteMember(User::find($userid));
        }
    }

    /**
     * Demote an owner of an organization to normal member.
     *
     * @param Command $command
     *
     * @command organization.owners.destroy
     */
    public function handleDemoteMember(Command $command)
    {
        $organization = Organization::find($command->getObjectId());

        foreach ($command->getData('users') as $userid) {
            $organization->demoteMember(User::find($userid));
        }
    }
}
