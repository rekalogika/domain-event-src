<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withPaths([
        __DIR__ . '/packages',
        __DIR__ . '/tests',
    ])
    ->withImportNames(importShortClasses: false)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
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
        // potential cognitive burden
        FlipTypeControlToUseExclusiveTypeRector::class,

        // results in too long variables
        CatchExceptionNameMatchingTypeRector::class,

        // makes code unreadable
        DisallowedShortTernaryRuleFixerRector::class,
        
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__ . '/tests/Integration/EventListener/*',
            __DIR__ . '/tests/Framework/EventListener/*',
        ],
    ]);
