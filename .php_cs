<?php
return PhpCsFixer\Config::create()
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
    'php_unit_test_annotation' => [
        'style' => 'annotation',
    ],
    'php_unit_method_casing' => [
        'case' => 'snake_case',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'this',
    ],

    // Additional Rules
    'concat_space' => ['spacing' => 'one'],
])
->setFinder(PhpCsFixer\Finder::create()
    ->in(['app', 'tests'])
);
