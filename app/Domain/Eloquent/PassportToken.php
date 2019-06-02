<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

/**
 * Read-only model - Use \Laravel\Passport\Token for non-read operations
 */
final class PassportToken extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'oauth_access_tokens';

    /**
     * {@inheritdoc}
     */
    protected $resource_key = 'tokens';

    /**
     * {@inheritdoc}
     */
    protected $guarded = ['*'];

    /**
     * {@inheritdoc}
     */
    protected $hidden = [
        'user_id',
        'client_id',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'scopes'  => 'array',
        'revoked' => 'bool',
    ];

    /**
     * {@inheritdoc}
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public function save(array $options = [])
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributes = [], array $options = [])
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return false;
    }
}
