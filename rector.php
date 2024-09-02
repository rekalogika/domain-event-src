<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withPaths([
        __DIR__ . '/packages',
        __DIR__ . '/tests',
    ])
    ->withImportNames(importShortClasses: false)
    ->withCodeQualityLevel(20)
    ->withPreparedSets(
        deadCode: true,
        // codeQuality: true,
        // codingStyle: true,
        typeDeclarations: true,
        // privatization: true,
        // instanceOf: true,
        // strictBooleans: true,
        // symfonyCodeQuality: true,
        // doctrineCodeQuality: true,
    )
    ->withPhpSets(php81: true)
    ->withRules([
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withSkip([
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__ . '/tests/Integration/EventListener/*',
            __DIR__ . '/tests/Framework/EventListener/*',
        ],
    ]);
