<?php

use Faker\Generator as Faker;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

/** @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(User::class, function (Faker $faker) {
    return [
        'id'            => $faker->uuid,
        'username'      => $faker->userName,
        'email'         => $faker->companyEmail,
        'password'      => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'administrator' => false,
    ];
});
