<?php

use Faker\Generator as Faker;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;

/** @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Organization::class, function (Faker $faker) {
    return [
        'name'         => $faker->slug,
        'display_name' => $faker->company,
        'description'  => $faker->text(50),
    ];
});
