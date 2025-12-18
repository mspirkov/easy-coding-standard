<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPhpCsFixerSets(perCS: true)
    ->withConfiguredRule(OrderedImportsFixer::class, [
        'case_sensitive' => true,
        'imports_order' => ['const', 'class', 'function'],
        'sort_algorithm' => 'alpha',
    ]);
