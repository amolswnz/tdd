<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use SilverStripe\Dev\Deprecation;
use Netwerkstatt\SilverstripeRector\Set\SilverstripeSetList;
use Netwerkstatt\SilverstripeRector\Set\SilverstripeLevelSetList;

Deprecation::enable();

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app/_config.php',
        __DIR__ . '/app/src',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withSets([
        SilverstripeSetList::CODE_STYLE,
        SilverstripeLevelSetList::UP_TO_SS_6_0,
    ])
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
