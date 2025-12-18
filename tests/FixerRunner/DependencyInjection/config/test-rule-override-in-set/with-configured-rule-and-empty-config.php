<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPhpCsFixerSets(perCS: true)
    ->withConfiguredRule(OrderedImportsFixer::class, []);
