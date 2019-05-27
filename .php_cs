<?php
return PhpCsFixer\Config::create()
->setUsingCache(false)
->setRules([
    // Base Rule Sets
    '@PSR2' => true,
    '@Symfony' => true,

    // Rule Overrides
    'phpdoc_summary' => false,

    // Additional Rules
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => [
        'align_double_arrow' => true, 
        'align_equals' => null
    ],
    'concat_space' => ['spacing' => 'one'],
    'ordered_imports' => true,
])
->setFinder(PhpCsFixer\Finder::create()
    ->in(['app', 'tests'])
);