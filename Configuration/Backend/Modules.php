<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later

/*
  * This file is part of the package cru/psr14-event-list.
  *
  * Copyright (C) 2024 - 2025 Christian Rath-Ulrich
  *
  * It is free software; you can redistribute it and/or modify it under
  * the terms of the GNU General Public License, either version 3
  * of the License, or any later version.
  *
  * For the full copyright and license information, please read the
  * LICENSE file that was distributed with this source code.
  */

use Cru\Psr14EventList\Backend\Controller\AdminModuleController;

return [
    'psr14_event_list' => [
        'parent' => 'system',
        'position' => ['top'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/system/psr14_event_list',
        'labels' => 'LLL:EXT:psr14_event_list/Resources/Private/Language/Module/locallang_mod.xlf',
        'extensionName' => 'Psr14EventList',
        'iconIdentifier' => 'tx_psr14_event_list-backend-module',
        'routes' => [
            '_default' => [
                'target' => AdminModuleController::class . '::handleRequest',
            ],
        ],
    ],
];
