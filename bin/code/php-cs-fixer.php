<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->in(['src'])
    ->exclude('var');

$config = new PhpCsFixer\Config();
$config->setParallelConfig(ParallelConfigFactory::detect());
$config
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => true,
        'class_attributes_separation' => ['elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
            'trait_import' => 'one',
        ]],
        'class_definition' => false,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'fopen_flags' => ['b_mode' => true],
        'general_phpdoc_annotation_remove' => ['annotations' => ['author', 'copyright', 'package', 'group']],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_extra_blank_lines' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'single_line_throw' => false,
        'trailing_comma_in_multiline' => false,
        'types_spaces' => ['space' => 'single'],
        'yoda_style' => false,
    ])
    ->setFinder($finder);

return $config;
