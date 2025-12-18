<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\FixerRunner\DependencyInjection;

use Iterator;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PHPUnit\Framework\Attributes\DataProvider;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyCodingStandard\Utils\PrivatesAccessorHelper;

final class FixerServiceRegistrationTest extends AbstractTestCase
{
    public function test(): void
    {
        $this->createContainerWithConfigs([__DIR__ . '/config/easy-coding-standard.php']);
        $fixerFileProcessor = $this->make(FixerFileProcessor::class);

        $checkers = $fixerFileProcessor->getCheckers();

        $this->assertCount(2, $checkers);

        /** @var ArraySyntaxFixer $arraySyntaxFixer */
        $arraySyntaxFixer = $checkers[1];
        $this->assertInstanceOf(ArraySyntaxFixer::class, $arraySyntaxFixer);

        $arraySyntaxConfiguration = PrivatesAccessorHelper::getPropertyValue($arraySyntaxFixer, 'configuration');
        $this->assertSame([
            'syntax' => 'short',
        ], $arraySyntaxConfiguration);

        /** @var VisibilityRequiredFixer $visibilityRequiredFixer */
        $visibilityRequiredFixer = $checkers[0];
        $this->assertInstanceOf(VisibilityRequiredFixer::class, $visibilityRequiredFixer);

        $visibilityRequiredConfiguration = PrivatesAccessorHelper::getPropertyValue(
            $visibilityRequiredFixer,
            'configuration'
        );

        $this->assertSame([
            'elements' => ['property'],
        ], $visibilityRequiredConfiguration);
    }

    /**
     * See https://github.com/easy-coding-standard/easy-coding-standard/discussions/198
     *
     * @param array{
     *     case_sensitive: bool,
     *     imports_order: list<string>|null,
     *     sort_algorithm: string,
     * } $expectedConfiguration
     */
    #[DataProvider('provideRuleOverrideInSetData')]
    public function testRuleOverrideInSet(string $filename, array $expectedConfiguration): void
    {
        $this->createContainerWithConfigs([__DIR__ . '/config/test-rule-override-in-set/' . $filename]);
        $fixerFileProcessor = $this->make(FixerFileProcessor::class);

        $checkers = $fixerFileProcessor->getCheckers();
        $orderedImportsFixerInstances = [];

        foreach ($checkers as $checker) {
            if ($checker instanceof OrderedImportsFixer) {
                $orderedImportsFixerInstances[] = $checker;
            }
        }

        $this->assertCount(2, $orderedImportsFixerInstances);

        foreach ($orderedImportsFixerInstances as $orderedImportFixerInstance) {
            $configuration = PrivatesAccessorHelper::getPropertyValue($orderedImportFixerInstance, 'configuration');

            $this->assertSame($expectedConfiguration, $configuration);
        }
    }

    public static function provideRuleOverrideInSetData(): Iterator
    {
        yield [
            'with-rules.php',
            [
                'case_sensitive' => false,
                'imports_order' => null,
                'sort_algorithm' => OrderedImportsFixer::SORT_ALPHA,
            ],
        ];

        yield [
            'with-configured-rule.php',
            [
                'case_sensitive' => true,
                'imports_order' => ['const', 'class', 'function'],
                'sort_algorithm' => 'alpha',
            ],
        ];

        yield [
            'with-configured-rule-and-empty-config.php',
            [
                'case_sensitive' => false,
                'imports_order' => null,
                'sort_algorithm' => OrderedImportsFixer::SORT_ALPHA,
            ],
        ];
    }
}
