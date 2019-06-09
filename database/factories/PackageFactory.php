<?php

use Faker\Generator as Faker;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;

/** @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Package::class, function (Faker $faker) {
    return [
        'id'       => $faker->uuid,
        'name'     => $faker->slug,
        'source'   => sprintf('git@github.com:%s/%s.git', $faker->slug, $faker->slug),
        'ref'      => $faker->randomElement(['master', 'development', '@tag']),
        'schedule' => $faker->randomElement(['nightly', 'weekly', 'hook', 'none']),
    ];
});
