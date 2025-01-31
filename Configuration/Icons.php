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


use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tx_psr14_event_list-backend-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:psr14_event_list/Resources/Public/Images/BackendModule.svg',
    ],
];
