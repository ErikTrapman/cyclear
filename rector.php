<?php

use Rector\Symfony\Set\SymfonySetList;
use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

    $rectorConfig->sets([
//        SymfonySetList::SYMFONY_62,
//        SymfonySetList::SYMFONY_CODE_QUALITY,
//        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
//        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
//        TemplateAnnotationToThisRenderRector::class,
    ]);
//    $rectorConfig->rule(TemplateAnnotationToThisRenderRector::class);
};
