<?php

use \PhpCsFixer\Config;

return Config::create()
->setUsingCache(false)
->setRiskyAllowed(true)
->setRules([
    // Base Rule Sets
    '@PSR2'             => true,
    '@Symfony'          => true,
    '@PhpCsFixer'       => true,
    '@Symfony:risky'    => true,
    '@PhpCsFixer:risky' => true,

    // Rule Overrides
    'array_indentation'      => false,
    'binary_operator_spaces' => [
        'default' => 'align_single_space_minimal',
        'operators' => [
            '=' => null,
        ],
    ],
    'multiline_whitespace_before_semicolons' => [
        'strategy' => 'no_multi_line',
    ],

    // Additional Rules
    'concat_space' => ['spacing' => 'one'],
])
->setFinder(PhpCsFixer\Finder::create()
    ->in(['app', 'tests'])
);
