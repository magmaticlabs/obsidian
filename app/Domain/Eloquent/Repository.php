<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Repository extends Model
{
    /**
     * Organization relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Packages relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'repository_id');
    }
}
