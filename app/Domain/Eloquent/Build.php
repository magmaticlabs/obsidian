<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Build extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'start_time'      => 'datetime',
        'completion_time' => 'datetime',
    ];

    /**
     * Package relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
