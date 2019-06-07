<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
