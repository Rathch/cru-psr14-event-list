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

namespace Cru\Psr14EventList\Command;

use Cru\Psr14EventList\Service\ProvideEventListService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class EventListCommand extends Command
{
    public function __construct(
        private ProvideEventListService $provideEventListService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Shows available PSR-14 Events')
            ->addOption(
                'no-cache',
                '',
                InputOption::VALUE_NONE,
                'Disable caching (lower performance, up-to-date results)',
                null,
            )
            ->addOption(
                'no-docs',
                '',
                InputOption::VALUE_NONE,
                'Disable fetching Documentation link (faster performance)',
                null,
            )
            ->addOption(
                'no-table-separator',
                '',
                InputOption::VALUE_NONE,
                'Prevent a table separator',
                null,
            )
            ->addOption(
                'no-table',
                '',
                InputOption::VALUE_NONE,
                'Do not use table output, plaintext',
                null,
            )
            ->addOption(
                'vertical-table',
                '',
                InputOption::VALUE_NONE,
                'Use a vertical table',
                null,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finalStats = [];
        $useCache = ($input->getOption('no-cache') !== true);
        if ($useCache) {
            $finalStats[] = 'Cached';
            $output->writeln('<info>PSR-14 event list (cached)</info>');
        } else {
            $finalStats[] = 'Live';
            $output->writeln('<info>PSR-14 event list (live)</info>');
        }

        $fetchDocs = ($input->getOption('no-docs') !== true);
        if ($fetchDocs) {
            $output->writeln('<info>Utilizing documentation links</info>');
        } else {
            $finalStats[] = 'Skipped docs';
            $output->writeln('<info>Skipping documentation links</info>');
        }

        $eventList = $this->provideEventListService->getConfiguration($useCache, $fetchDocs, $output);

        $output->writeln('Got <info>' . count($eventList) . '</info> events.');

        if ($input->getOption('no-table') === true) {
            $table = null;
        } else {
            $table = new Table($output);
            if ($input->getOption('vertical-table') === true) {
                $table->setVertical();
            }
            $cols = ['Package' => 'package', 'EventClass' => 'label'];
            if ($fetchDocs) {
                $cols['Documentation'] = 'documentation';
            }
            $table->setHeaders(array_keys($cols));
        }

        $last = null;
        $errors = [];
        foreach ($eventList as $event) {
            if ($last === null) {
                $last = $event['package'];
            }

            if ($table === null) {
                $output->writeln('<info>' . $event['label'] . '</info>');
                if ($fetchDocs) {
                    if ($event['documentation'] === '#none-found') {
                        $output->writeln(' -> <error>N/A</error>');
                        $errors[$event['label']] = true;
                    } else {
                        $output->writeln(' -> ' . $event['documentation']);
                    }
                }
            } else {
                if ($last !== $event['package'] && $input->getOption('no-table-separator') !== true) {
                    $table?->addRow(new TableSeparator());
                }
                $row = [];
                foreach ($cols as $rowColumn) {
                    $row[] = $event[$rowColumn] ?? 'N/A';
                }
                $table->addRow($row);
            }
            $last = $event['package'];
        }

        $table?->render();

        if (count($errors) > 0) {
            $finalStats[] = '<error>' . count($errors) . '</error> missing doc links.';
        }

        $output->writeln('');
        $output->writeln('<info>Total:</info> ' . count($eventList) . ' events. ' . implode(', ', $finalStats));


        return Command::SUCCESS;
    }
}
