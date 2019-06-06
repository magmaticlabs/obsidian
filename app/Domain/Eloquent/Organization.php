<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Organization extends Model
{
    /**
     * Repositories relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class, 'organization_id');
    }

    /**
     * Members relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_memberships', 'organization_id', 'user_id');
    }

    /**
     * Owners relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_memberships', 'organization_id', 'user_id')->wherePivot('owner', '=', true);
    }

    /**
     * Determines if the given user is a member of the organization
     *
     * @param User $user
     *
     * @return bool
     */
    public function hasMember(User $user)
    {
        return null != $this->members()->find($user->id);
    }

    /**
     * Determines if the given user is an owner of the organization
     *
     * @param User $user
     *
     * @return bool
     */
    public function hasOwner(User $user)
    {
        return null != $this->owners()->find($user->id);
    }

    /**
     * Add a member to the organization
     *
     * @param \MagmaticLabs\Obsidian\Domain\Eloquent\User $user
     */
    public function addMember(User $user): void
    {
        $this->members()->syncWithoutDetaching([
            $user->getKey() => ['owner' => false], ]
        );
    }

    /**
     * Remove a member from the organization
     *
     * @param \MagmaticLabs\Obsidian\Domain\Eloquent\User $user
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach([$user->getKey()]);
    }

    /**
     * Promote a member of the organization to owner
     *
     * @param \MagmaticLabs\Obsidian\Domain\Eloquent\User $user
     *
     * @throws \InvalidArgumentException
     */
    public function promoteMember(User $user): void
    {
        $userid = $user->getKey();
        if (0 == $this->members()->where('id', $userid)->count()) {
            throw new \InvalidArgumentException('User is not a member of the organization');
        }

        $this->members()->syncWithoutDetaching([
            $userid => ['owner' => true], ]
        );
    }

    /**
     * Demote an owner of the organization to member
     *
     * @param \MagmaticLabs\Obsidian\Domain\Eloquent\User $user
     *
     * @throws \InvalidArgumentException
     */
    public function demoteMember(User $user): void
    {
        $userid = $user->getKey();
        if (0 == $this->members()->where('id', $userid)->count()) {
            throw new \InvalidArgumentException('User is not a member of the organization');
        }

        $this->members()->syncWithoutDetaching([
                $userid => ['owner' => false], ]
        );
    }
}
