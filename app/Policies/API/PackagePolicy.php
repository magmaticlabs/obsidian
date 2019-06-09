<?php

namespace MagmaticLabs\Obsidian\Policies\API;

use MagmaticLabs\Obsidian\Domain\Eloquent\Package as Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

final class PackagePolicy
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
        return true; // All users can view any individual package
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
        return true; // Allow all users to create packages
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
        return $model->repository->organization->hasMember($user);
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
        return $model->repository->organization->hasMember($user);
    }

    // --

    /**
     * Determine whether the user can view the repository relationship collection.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function repository_index(User $user, Model $model): bool
    {
        return true; // All users can view the collection
    }

    // --

    /**
     * Determine whether the user can view the builds relationship collection.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return bool
     */
    public function builds_index(User $user, Model $model): bool
    {
        return true; // All users can view the collection
    }
}
