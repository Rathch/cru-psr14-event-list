<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Cru\Psr14EventList\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProvideEventListService
{
    public function __construct() {}

    public function getConfiguration(): array
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $eventClasses = [];

        foreach ($packageManager->getActivePackages() as $package) {
            $classesPath = $package->getPackagePath() . 'Classes/';
            if (is_dir($classesPath)) {
                $finder = new \Symfony\Component\Finder\Finder();
                $finder->files()->in($classesPath)->name('*Event.php');

                foreach ($finder as $file) {
                    $content = file_get_contents($file->getRealPath());

                    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                        $namespace = $matches[1];
                        $className = $namespace . '\\' . $file->getBasename('.php');
                        $classNameDoc = str_replace('\\', '/', $className);
                        $classNameDoc = str_replace('TYPO3/CMS/', '', $classNameDoc);
                        $classNameDoc = str_replace('/Event/', '/', $classNameDoc);

                        if (class_exists($className) && !str_contains($className, 'Abstract')) {
                            $eventClasses[$className] = [
                                'label' => $className,
                                'package' => $package->getPackageKey(),
                            ];

                            $cacheFile = Environment::getVarPath() . '/cache/data/events_docs.json';
                            $docsCache = [];
                            if (file_exists($cacheFile)) {
                                $docsCache = json_decode(file_get_contents($cacheFile), true) ?? [];
                            }

                            $cacheKey = md5($classNameDoc);
                            if (!isset($docsCache[$cacheKey]) || $docsCache[$cacheKey]['expires'] < time()) {
                                $docUrl = 'https://docs.typo3.org/m/typo3/reference-coreapi/13.4/en-us/ApiOverview/Events/Events/' . $classNameDoc . '.html';
                                $headers = get_headers($docUrl, true);
                                $exists = $headers && str_contains($headers[0], '200');

                                $docsCache[$cacheKey] = [
                                    'url' => $exists ? $docUrl : '',
                                    'expires' => time() + 86400,
                                ];

                                GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($docsCache));
                            }

                            if ($docsCache[$cacheKey]['url'] !== '') {
                                $eventClasses[$className]['documentation'] = $docsCache[$cacheKey]['url'];
                            }
                        }
                    }
                }
            }
        }

        return $eventClasses;
    }
}
