<?php

declare(strict_types=1);

namespace Cru\Psr14EventList\Tests\Functional\Command;

use Cru\Psr14EventList\Command\EventListCommand;
use Cru\Psr14EventList\Service\ProvideEventListService;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\EventProvider\Event\ApplicationReadyEvent;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\SecondaryEventProvider\Event\SecondaryFixtureEvent;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class EventListCommandTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psr14_event_list',
        'typo3conf/ext/psr14_event_list/Tests/Functional/Fixtures/Extensions/event_provider',
        'typo3conf/ext/psr14_event_list/Tests/Functional/Fixtures/Extensions/secondary_event_provider',
    ];

    #[Test]
    public function executeListsEventsAsPlainText(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $exitCode = $commandTester->execute([
            '--no-docs' => true,
            '--no-table' => true,
        ]);

        $display = $commandTester->getDisplay();
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('PSR-14 event list (cached)', $display);
        self::assertStringContainsString('Skipping documentation links', $display);
        self::assertStringContainsString(ApplicationReadyEvent::class, $display);
        self::assertStringContainsString(SecondaryFixtureEvent::class, $display);
        self::assertStringContainsString('Total:', $display);
        self::assertStringContainsString('Skipped docs', $display);
    }

    #[Test]
    public function executeListsEventsInTableOutput(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $exitCode = $commandTester->execute([
            '--no-docs' => true,
        ]);

        $display = $commandTester->getDisplay();
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Package', $display);
        self::assertStringContainsString('EventClass', $display);
        self::assertStringContainsString('event_provider', $display);
        self::assertStringContainsString('secondary_event_provider', $display);
        self::assertStringContainsString(ApplicationReadyEvent::class, $display);
        self::assertStringContainsString(SecondaryFixtureEvent::class, $display);
    }

    #[Test]
    public function executeListsEventsInVerticalTableOutput(): void
    {
        $commandTester = new CommandTester($this->createCommand());

        $exitCode = $commandTester->execute([
            '--no-docs' => true,
            '--vertical-table' => true,
        ]);

        $display = $commandTester->getDisplay();
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Package', $display);
        self::assertStringContainsString('EventClass', $display);
        self::assertStringContainsString(ApplicationReadyEvent::class, $display);
    }

    #[Test]
    public function executeShowsMissingDocumentationWhenRequested(): void
    {
        $this->writeStaleDocumentationCache([]);

        $commandTester = new CommandTester($this->createCommand());

        $exitCode = $commandTester->execute([
            '--no-table' => true,
            '--show-missing' => true,
        ]);

        $display = $commandTester->getDisplay();
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Utilizing documentation links', $display);
        self::assertStringContainsString('Missing doc links:', $display);
        self::assertStringContainsString('- ' . ApplicationReadyEvent::class, $display);
        self::assertStringContainsString('missing doc links', $display);
    }

    private function createCommand(): EventListCommand
    {
        return new EventListCommand(
            new ProvideEventListService(
                GeneralUtility::makeInstance(PackageManager::class),
                new Typo3Version(),
            ),
        );
    }

    private function writeStaleDocumentationCache(array $cache): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/events_docs.json';
        GeneralUtility::mkdir_deep(dirname($cacheFile));
        file_put_contents($cacheFile, json_encode($cache, JSON_THROW_ON_ERROR));
        touch($cacheFile, time() - 90000);
    }
}
