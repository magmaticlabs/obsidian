<?php

use Faker\Generator as Faker;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;

/** @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Build::class, function (Faker $faker) {
    return [
        'id'        => $faker->uuid,
        'ref'       => $faker->slug,
        'commit'    => sha1($faker->text(50)),
        'status'    => $faker->randomElement(['pending', 'running', 'success', 'failure']),
    ];
})->afterMaking(Build::class, function(Build $build, Faker $faker) {
    switch ($build->status) {
        case 'running':
            $build->start_time = $faker->dateTimeThisMonth();
            break;
        case 'success':
        case 'failure':
            $build->start_time = $faker->dateTimeThisMonth();
            $build->completion_time = $build->start_time->add(new DateInterval(sprintf('PT%dS', $faker->randomNumber())));
            break;
    }
});
