<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/packages/domain-event/config')
    ->in(__DIR__ . '/packages/domain-event/src')
    ->in(__DIR__ . '/packages/domain-event-contracts/src')
    ->in(__DIR__ . '/packages/domain-event-outbox/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],

    // imports
    'fully_qualified_strict_types' => true,
    'global_namespace_import' => [
        'import_classes' => false,
        'import_constants' => false,
        'import_functions' => false,
    ],
    'no_leading_import_slash' => true,
    'no_unneeded_import_alias' => true,
    'no_unused_imports' => true,
    'single_line_empty_body' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['const', 'class', 'function']
    ],
    'single_line_after_imports' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'declare_strict_types' => true,
    'native_function_invocation' => ['include' => ['@compiler_optimized']],
    'header_comment' => [
        'header' => <<<EOF
This file is part of rekalogika/domain-event-src package.

(c) Priyadi Iman Nurcahyo <https://rekalogika.dev>

For the full copyright and license information, please view the LICENSE file
that was distributed with this source code.
EOF,
    ]
])
    ->setFinder($finder);
