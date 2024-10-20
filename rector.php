<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    //
    ->withSets([
            \Rector\Symfony\Set\SymfonySetList::SYMFONY_71,
            \Rector\Symfony\Set\SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        ]
    )
    ->withTypeCoverageLevel(0)
    ->withAttributesSets(symfony: true, doctrine: true);
