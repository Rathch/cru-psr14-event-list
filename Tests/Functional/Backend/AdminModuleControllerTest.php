<?php

declare(strict_types=1);

namespace Cru\Psr14EventList\Tests\Functional\Backend;

use Cru\Psr14EventList\Backend\Controller\AdminModuleController;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class AdminModuleControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psr14_event_list',
    ];

    #[Test]
    public function renderTreeRendersEscapedNestedHtmlForSupportedValueTypes(): void
    {
        $html = $this->renderTree([
            'plain' => 'value',
            'escaped' => '<strong>value</strong>',
            'empty' => [],
            'nested' => [
                'child' => 'nestedValue',
            ],
            'backedEnum' => RenderTreeBackedFixtureEnum::Active,
            'unitEnum' => RenderTreeUnitFixtureEnum::Enabled,
            'object' => (object)[
                'property' => 'objectValue',
            ],
            'callback' => static function (): void {},
        ]);

        self::assertStringContainsString('<ul class="treelist">', $html);
        self::assertStringContainsString('<span class="treelist-label">plain</span>', $html);
        self::assertStringContainsString('<span class="treelist-value">value</span>', $html);
        self::assertStringContainsString('&lt;strong&gt;value&lt;/strong&gt;', $html);
        self::assertStringContainsString('data-bs-target="#collapse-list-', $html);
        self::assertStringContainsString('<span class="treelist-label">child</span>', $html);
        self::assertStringContainsString('<span class="treelist-value">active</span>', $html);
        self::assertStringContainsString('<span class="treelist-value">Enabled</span>', $html);
        self::assertStringContainsString('<span class="treelist-label">property</span>', $html);
        self::assertStringContainsString('<span class="treelist-value">objectValue</span>', $html);
        self::assertStringContainsString('anonymous callback function', $html);
    }

    #[Test]
    public function renderTreeWrapsNestedTreesInPersistedCollapseContainer(): void
    {
        $html = $this->renderTree(['child' => 'value'], 'incoming-identifier');

        self::assertStringContainsString('class="treelist-collapse collapse"', $html);
        self::assertStringContainsString('data-persist-collapse-state-suffix="lowlevel-configuration-event-list"', $html);
        self::assertStringContainsString('id="collapse-list-incoming-identifier"', $html);
    }

    private function renderTree(array $tree, string $incomingIdentifier = ''): string
    {
        $controller = (new \ReflectionClass(AdminModuleController::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(AdminModuleController::class, 'renderTree');

        return (string)$method->invoke($controller, $tree, 'event-list', $incomingIdentifier);
    }
}

enum RenderTreeBackedFixtureEnum: string
{
    case Active = 'active';
}

enum RenderTreeUnitFixtureEnum
{
    case Enabled;
}
