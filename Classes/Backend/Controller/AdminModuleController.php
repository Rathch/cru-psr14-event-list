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

namespace Cru\Psr14EventList\Backend\Controller;

use Cru\Psr14EventList\Service\ProvideEventListService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsController]
final class AdminModuleController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ProvideEventListService $provideEventListService,
    ) {}

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        return $this->indexAction($request);
    }

    private function setUpMenu(ServerRequestInterface $request, ModuleTemplate $moduleTemplate): void
    {
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('cruPsr14EventList');

        $menuItems = [
            'index' => [
                'controller' => 'Module',
                'action' => 'index',
                'route' => 'tx_psr14_event_list_index',
                'label' => 'Index',
            ],
            'list' => [
                'controller' => 'Module',
                'action' => 'listCoreEventsAction',
                'route' => 'tx_psr14_event_list_list',
                'label' => 'List',
            ],
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        foreach ($menuItems as $menuItemConfig) {
            $currentUri = $request->getUri();
            $action = $menuItemConfig['route'];
            $uri = $uriBuilder->buildUriFromRoute($action, [$request]);
            $isActive = ($currentUri === $uri);
            $menuItem = $menu->makeMenuItem()
                            ->setTitle($menuItemConfig['label'])
                            ->setHref($uri)
                            ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    public function indexAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $languageService = $this->getLanguageService();

        $translations = [
            'overview_title' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:overview_title'),
            'overview_intro' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:overview_intro'),
            'overview_details' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:overview_details'),
            'documentation_link' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:documentation_link'),
            'documentation_text' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:documentation_text'),
            'loading_warning' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:loading_warning'),
            'show_events' => $languageService->sL('LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf:show_events'),
        ];

        $moduleTemplate->assign('translations', $translations);
        return $moduleTemplate->renderResponse('AdminModule/Index');
    }

    public function listCoreEventsAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);
        $eventList = $this->provideEventListService->getConfiguration();
        $labelHash = hash('md5', 'eventList');
        $moduleTemplate->assignMultiple([
            'eventList' => $eventList,
            'tree'      => $this->renderTree($eventList, $labelHash),
            'labelHash' => $labelHash,
            'treeName'  => 'Core Events',
        ]);

        return $moduleTemplate->renderResponse('AdminModule/List');
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * We're rendering the trees directly in PHP for two reasons:
     * - Performance of Fluid is not good enough when dealing with large trees like TCA
     * - It's a bit hard to deal with the object details in Fluid
     */
    private function renderTree(array|\ArrayObject $tree, string $labelHash, string $incomingIdentifier = ''): string
    {
        $html = '';
        if (!empty($incomingIdentifier)) {
            $html .= '<div' .
                ' class="treelist-collapse collapse"' .
                ' data-persist-collapse-state="true"' .
                ' data-persist-collapse-state-suffix="lowlevel-configuration-' . $labelHash . '"' .
                ' data-persist-collapse-state-if-state="shown"' .
                ' data-persist-collapse-state-not-if-search="true"' .
                ' id="collapse-list-' . $incomingIdentifier . '">';
        }

        $html .= '<ul class="treelist">';

        foreach ($tree as $key => $value) {
            $callableName = '';
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof \UnitEnum) {
                $value = $value->name;
            } elseif (is_callable($value, false, $callableName)) {
                $value = $callableName;
                if ($callableName === 'Closure::__invoke') {
                    $value .= ' (anonymous callback function: function() {})';
                }
            } elseif (is_object($value) && !$value instanceof \Traversable) {
                $value = (array)$value;
            }
            $isValueIterable = is_iterable($value);

            $html .= '<li>';
            $newIdentifier = '';
            if ($isValueIterable && !empty($value)) {
                $newIdentifier = hash('xxh3', $incomingIdentifier . $key);
                $html .= '
                    <typo3-backend-tree-node-toggle
                        class="treelist-control collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-list-' . $newIdentifier . '"
                        aria-expanded="false">
                    </typo3-backend-tree-node-toggle>';
            }
            $html .= '<span class="treelist-group treelist-group-monospace">';
            $html .= '<span class="treelist-label">' . htmlspecialchars((string)$key) . '</span>';
            if (!$isValueIterable) {
                $html .= ' <span class="treelist-operator">=</span> <span class="treelist-value">' . htmlspecialchars((string)$value) . '</span>';
            }
            if ($isValueIterable && empty($value)) {
                $html .= ' <span class="treelist-operator">=</span>';
            }
            $html .= '</span>';
            if ($isValueIterable && !empty($value)) {
                $html .= $this->renderTree($value, $labelHash, $newIdentifier);
            }
            $html .= '</li>';
        }

        $html .= '</ul>';

        if (!empty($incomingIdentifier)) {
            $html .= '</div>';
        }

        return $html;
    }
}
