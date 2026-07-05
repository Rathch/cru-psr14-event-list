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

    $pathsToCreate = [
        ORIGINAL_ROOT . 'typo3temp/var/tests',
        ORIGINAL_ROOT . 'typo3temp/var/transient',
    ];

    $composerConfigurationPath = ORIGINAL_ROOT . 'composer.json';
    if (is_file($composerConfigurationPath)) {
        $composerConfiguration = json_decode((string)file_get_contents($composerConfigurationPath), true);
        $webDir = $composerConfiguration['extra']['typo3/cms']['web-dir'] ?? 'public';
        $webRoot = ORIGINAL_ROOT . $webDir;
        $pathsToCreate[] = $webRoot . '/typo3conf/ext';
        $pathsToCreate[] = $webRoot . '/typo3temp/var/tests';
        $pathsToCreate[] = $webRoot . '/typo3temp/var/transient';
        $pathsToCreate[] = $webRoot . '/typo3temp/var/tests/functional-sqlite-dbs';
    }

    foreach ($pathsToCreate as $path) {
        $testbase->createDirectory($path);
    }
})();
