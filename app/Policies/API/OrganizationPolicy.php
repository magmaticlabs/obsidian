<?php

namespace MagmaticLabs\Obsidian\Policies\API;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization as Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

final class OrganizationPolicy
{
    /**
     * Determine whether the user can view the collection.
     *
     * @param User $user
     *
     * @return bool
     */
    public function index(User $user): bool
    {
        return true; // All users can view the collection
    }

    /**
     * Determine whether the user can view a resource.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function show(User $user, Model $model): bool
    {
        return true; // All users can view any individual organization
    }

    /**
     * Determine whether the user can create new resources.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // Allow all users to create organizations
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function update(User $user, Model $model): bool
    {
        return $model->hasOwner($user);
    }

    /**
     * Determine whether the user can delete the resource.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function destroy(User $user, Model $model): bool
    {
        return $model->hasOwner($user);
    }

    // --

    /**
     * Determine whether the user can view the members relationship collection.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function members_index(User $user, Model $model): bool
    {
        return true; // All users can view the collection
    }

    /**
     * Determine whether the user can create new member relationships.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function members_create(User $user, Model $model): bool
    {
        return $model->hasOwner($user);
    }

    /**
     * Determine whether the user can delete member relationships.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function members_destroy(User $user, Model $model)
    {
        return $model->hasOwner($user);
    }

    // --

    /**
     * Determine whether the user can view the owners relationship collection.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function owners_index(User $user, Model $model): bool
    {
        return true; // All users can view the collection
    }

    /**
     * Determine whether the user can create new owner relationships.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function owners_create(User $user, Model $model): bool
    {
        return $model->hasOwner($user);
    }

    /**
     * Determine whether the user can delete owner relationships.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function owners_destroy(User $user, Model $model)
    {
        return $model->hasOwner($user);
    }

    // --

    /**
     * Determine whether the user can view the repositories relationship collection.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function repositories_index(User $user, Model $model): bool
    {
        return true; // All users can view the collection
    }
}
