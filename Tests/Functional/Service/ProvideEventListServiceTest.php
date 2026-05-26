<?php

declare(strict_types=1);

namespace Cru\Psr14EventList\Tests\Functional\Service;

use Cru\Psr14EventList\Service\ProvideEventListService;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\EventProvider\Event\AbstractFixtureEvent;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\EventProvider\Event\ApplicationReadyEvent;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\EventProvider\Event\SubFolder\NestedFixtureEvent;
use Cru\Psr14EventList\Tests\Functional\Fixtures\Extensions\SecondaryEventProvider\Event\SecondaryFixtureEvent;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ProvideEventListServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psr14_event_list',
        'typo3conf/ext/psr14_event_list/Tests/Functional/Fixtures/Extensions/event_provider',
        'typo3conf/ext/psr14_event_list/Tests/Functional/Fixtures/Extensions/secondary_event_provider',
    ];

    private ProvideEventListService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ProvideEventListService(
            GeneralUtility::makeInstance(PackageManager::class),
            new Typo3Version(),
        );
    }

    #[Test]
    public function getConfigurationListsEventsFromActiveExtensions(): void
    {
        $configuration = $this->subject->getConfiguration(fetchDocs: false);

        self::assertArrayHasKey(ApplicationReadyEvent::class, $configuration);
        self::assertSame(ApplicationReadyEvent::class, $configuration[ApplicationReadyEvent::class]['label']);
        self::assertSame('event_provider', $configuration[ApplicationReadyEvent::class]['package']);

        self::assertArrayHasKey(NestedFixtureEvent::class, $configuration);
        self::assertSame(NestedFixtureEvent::class, $configuration[NestedFixtureEvent::class]['label']);
        self::assertSame('event_provider', $configuration[NestedFixtureEvent::class]['package']);

        self::assertArrayHasKey(SecondaryFixtureEvent::class, $configuration);
        self::assertSame(SecondaryFixtureEvent::class, $configuration[SecondaryFixtureEvent::class]['label']);
        self::assertSame('secondary_event_provider', $configuration[SecondaryFixtureEvent::class]['package']);
    }

    #[Test]
    public function getConfigurationIgnoresAbstractEventClasses(): void
    {
        $configuration = $this->subject->getConfiguration(fetchDocs: false);

        self::assertArrayNotHasKey(AbstractFixtureEvent::class, $configuration);
    }

    #[Test]
    public function getConfigurationMarksDocumentationAsSkippedWhenDocsAreDisabled(): void
    {
        $configuration = $this->subject->getConfiguration(fetchDocs: false);

        self::assertSame('#skipped', $configuration[ApplicationReadyEvent::class]['documentation']);
        self::assertSame('#skipped', $configuration[NestedFixtureEvent::class]['documentation']);
        self::assertSame('#skipped', $configuration[SecondaryFixtureEvent::class]['documentation']);
    }

    #[Test]
    public function getConfigurationUsesDocumentationCacheWhenAvailable(): void
    {
        $documentationPath = 'ApiOverview/Events/ApplicationReadyEvent.html';
        $cacheFile = Environment::getVarPath() . '/cache/data/events_docs.json';
        GeneralUtility::mkdir_deep(dirname($cacheFile));
        file_put_contents($cacheFile, json_encode([
            strtolower(str_replace('\\', '-', ApplicationReadyEvent::class)) => ['', '', $documentationPath],
        ], JSON_THROW_ON_ERROR));
        touch($cacheFile, time() - 90000);

        $configuration = $this->subject->getConfiguration();

        self::assertSame(
            'https://docs.typo3.org/m/typo3/reference-coreapi/' . (new Typo3Version())->getBranch() . '/en-us/' . $documentationPath,
            $configuration[ApplicationReadyEvent::class]['documentation'],
        );
    }

    #[Test]
    public function getConfigurationMarksDocumentationAsMissingWhenCacheContainsNoMatchingClass(): void
    {
        $this->writeStaleDocumentationCache([
            'some-unrelated-class' => ['', '', 'ApiOverview/Events/UnrelatedEvent.html'],
        ]);

        $configuration = $this->subject->getConfiguration();

        self::assertSame('#none-found', $configuration[ApplicationReadyEvent::class]['documentation']);
        self::assertSame('#none-found', $configuration[SecondaryFixtureEvent::class]['documentation']);
    }

    #[Test]
    public function getConfigurationWritesVerboseProgressForPackageScan(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

        $this->subject->getConfiguration(fetchDocs: false, cliOutput: $output);

        $display = $output->fetch();
        self::assertStringContainsString('Scanning', $display);
        self::assertStringContainsString('EXT:event_provider', $display);
        self::assertStringContainsString('EXT:secondary_event_provider', $display);
    }

    private function writeStaleDocumentationCache(array $cache): void
    {
        $cacheFile = Environment::getVarPath() . '/cache/data/events_docs.json';
        GeneralUtility::mkdir_deep(dirname($cacheFile));
        file_put_contents($cacheFile, json_encode($cache, JSON_THROW_ON_ERROR));
        touch($cacheFile, time() - 90000);
    }
}
