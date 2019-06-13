<?php

namespace MagmaticLabs\Obsidian\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use MagmaticLabs\Obsidian\Policies\API\BuildPolicy;
use MagmaticLabs\Obsidian\Policies\API\OrganizationPolicy;
use MagmaticLabs\Obsidian\Policies\API\PackagePolicy;
use MagmaticLabs\Obsidian\Policies\API\RepositoryPolicy;
use MagmaticLabs\Obsidian\Policies\API\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class         => UserPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Repository::class   => RepositoryPolicy::class,
        Package::class      => PackagePolicy::class,
        Build::class        => BuildPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
