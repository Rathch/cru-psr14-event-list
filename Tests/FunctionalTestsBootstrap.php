<?php

declare(strict_types=1);

(static function (): void {
    $autoloadPaths = [
        dirname(__DIR__) . '/.composer/vendor/autoload.php',
        dirname(__DIR__) . '/vendor/autoload.php',
    ];

    foreach ($autoloadPaths as $autoloadPath) {
        if (file_exists($autoloadPath)) {
            require $autoloadPath;
            break;
        }
    }

    $testbase = new \TYPO3\TestingFramework\Core\Testbase();
    $testbase->defineOriginalRootPath();
    $testbase->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/tests');
    $testbase->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/transient');
})();
