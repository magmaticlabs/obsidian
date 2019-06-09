<?php

namespace MagmaticLabs\Obsidian\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Policies\API\BuildPolicy;
use MagmaticLabs\Obsidian\Policies\API\OrganizationPolicy;
use MagmaticLabs\Obsidian\Policies\API\PackagePolicy;
use MagmaticLabs\Obsidian\Policies\API\RepositoryPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
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
