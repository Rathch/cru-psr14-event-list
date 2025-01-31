<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich, Garvin Hicking
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/psr14-event-list.
  *
  * Copyright (C) 2024 - 2025 Christian Rath-Ulrich, Garvin Hicking
  *
  * It is free software; you can redistribute it and/or modify it under
  * the terms of the GNU General Public License, either version 3
  * of the License, or any later version.
  *
  * For the full copyright and license information, please read the
  * LICENSE file that was distributed with this source code.
  */

declare(strict_types=1);

namespace Cru\Psr14EventList\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ProvideEventListService
{
    public function __construct(
        private PackageManager $packageManager,
        private Typo3Version $typo3Version,
    ) {}

    public function getConfiguration(bool $useCache = true, bool $fetchDocs = true, ?OutputInterface $cliOutput = null): array
    {
        $eventClasses = [];
        $docsUrl = 'https://docs.typo3.org/m/typo3/reference-coreapi/' . $this->typo3Version->getBranch() . '/en-us/';
        $docsUrlFallback = 'https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/';

        if ($fetchDocs) {
            if ($cliOutput?->isVerbose()) {
                $cliOutput->writeln('Using <info>' . $docsUrl . '</info> for doc lookup.');
            }
            $cacheFile = Environment::getVarPath() . '/cache/data/events_docs.json';

            // Do a live access if either caching is disabled, or the cachefile is missing or oudated.
            $docsCache = [];
            if ($useCache === true
                && file_exists($cacheFile)
                && filemtime($cacheFile) < time() - 86400
            ) {
                $docsCache = json_decode(file_get_contents($cacheFile), true);
                if ($cliOutput?->isVerbose()) {
                    $cliOutput->writeln('Retrieved <info>' . count($docsCache) . ' doc-links</info> from cache</info>');
                }
            } else {
                $jsonContents = GeneralUtility::getUrl($docsUrl . 'objects.inv.json');

                if ($jsonContents === false || $jsonContents === '') {
                    // Requesting versions like '14.1', '15.0' may lead to missing link.
                    // Use "main" as a fallback for the most recent version then.
                    $jsonContents = GeneralUtility::getUrl($docsUrlFallback . 'objects.inv.json');
                    if ($cliOutput?->isVerbose()) {
                        $cliOutput->writeln('Using fallback <info>' . $docsUrlFallback . '</info> for doc lookup.');
                    }
                }

                if ($jsonContents !== false && $jsonContents !== '') {
                    $docuJson = json_decode($jsonContents, true);
                    $docsCache = $docuJson['php:class'] ?? [];
                    GeneralUtility::writeFileToTypo3tempDir($cacheFile, json_encode($docsCache));
                    if ($cliOutput?->isVerbose()) {
                        $cliOutput->writeln('Persisted <info>' . count($docsCache) . ' doc-links</info> to cache');
                    }
                } elseif ($cliOutput?->isVerbose()) {
                    $cliOutput->writeln('<error>Could not write docs cache or invalid URL return.</error>');
                }
            }
        }

        $index = 0;
        foreach ($this->packageManager->getActivePackages() as $package) {
            $index++;
            if ($cliOutput?->isVerbose()) {
                $cliOutput->writeln('<info>#' . $index . '</info> Scanning <info>EXT:' . $package->getPackageKey() . '</info>');
            }

            $classesPath = $package->getPackagePath() . 'Classes/';
            if (is_dir($classesPath)) {
                $finder = new Finder();
                $finder->files()->in($classesPath)->name('*Event.php');

                foreach ($finder as $file) {
                    if ($cliOutput?->isVerbose()) {
                        $cliOutput->writeln(' - <info>EXT:' . $file . '</info>');
                    }
                    $content = file_get_contents($file->getRealPath());

                    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                        $namespace = $matches[1];
                        $className = $namespace . '\\' . $file->getBasename('.php');
                        $classNameDoc = strtolower(str_replace('\\', '-', ltrim($className, '\\')));

                        if (class_exists($className) && !str_contains($className, 'Abstract')) {
                            $eventClasses[$className] = [
                                'label' => $className,
                                'package' => $package->getPackageKey(),
                                'documentation' => '',
                            ];

                            if (!$fetchDocs) {
                                $eventClasses[$className]['documentation'] = '#skipped';
                                continue;
                            }

                            $eventClasses[$className]['documentation'] = '#none-found';
                            if (isset($docsCache[$classNameDoc][2])) {
                                $eventClasses[$className]['documentation'] = $docsUrl . $docsCache[$classNameDoc][2];
                                if ($cliOutput?->isVeryVerbose()) {
                                    $cliOutput->writeln('    - <info>Found <info>' . $classNameDoc . '</info> in docs php:class cache</info>');
                                }
                            } elseif ($cliOutput?->isVeryVerbose()) {
                                $cliOutput->writeln('    - <info>Did not find <info>' . $classNameDoc . '</info> in docs php:class cache</info>');
                            }
                        }
                    }
                }
            }

            if ($cliOutput?->isVerbose()) {
                $cliOutput->writeln('');
            }
        }

        return $eventClasses;
    }
}
