<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/_config.php',
        __DIR__ . '/src',
    ])
    ->withPreparedSets(
        common: true,
        psr12: true,
    )
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
        OrderedClassElementsFixer::class,
    ]);
