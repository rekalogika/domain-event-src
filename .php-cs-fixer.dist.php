<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/packages/domain-event/config')
    ->in(__DIR__ . '/packages/domain-event/src')
    ->in(__DIR__ . '/packages/domain-event/tests')
    ->in(__DIR__ . '/packages/domain-event-contracts/src');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'declare_strict_types' => true,
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
