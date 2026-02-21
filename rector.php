<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// https://getrector.com/documentation
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        deadCode: true,
        earlyReturn: true,
        instanceOf: true,
        naming: true,
        privatization: true,
        typeDeclarations: true,
    )
    ->withSkip([
        __DIR__.'/tests/Traits/Database.php',
    ]);
