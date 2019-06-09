<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Package extends Model
{
    /**
     * Repository relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repository_id');
    }

    /**
     * Builds relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function builds(): HasMany
    {
        return $this->hasMany(Build::class, 'package_id');
    }
}
