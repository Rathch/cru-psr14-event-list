<?php

declare(strict_types=1);

namespace Cru\Psr14EventList\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase as Typo3FunctionalTestCase;

abstract class AbstractFunctionalTestCase extends Typo3FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'lowlevel',
    ];
}
