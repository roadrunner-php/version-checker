<?php

declare(strict_types=1);

use Spiral\CodeStyle\Builder;

require_once 'vendor/autoload.php';


return Builder::create()
    ->include(__DIR__ . '/src')
    ->include(__FILE__)
    ->build()->setRules([
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'fully_qualified_strict_types' => true,
        'no_unused_imports' => true,
    ]);
