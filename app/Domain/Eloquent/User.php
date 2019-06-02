<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\HasApiTokens;

final class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasApiTokens;

    protected $casts = [
        'administrator' => 'bool',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->hidden = array_merge($this->getHidden(), [
            'password',
        ]);
    }
}
