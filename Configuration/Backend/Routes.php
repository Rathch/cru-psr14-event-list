<?php

return [
    'tx_psr14_event_list_list' => [
        'path' => '/psr14/event/list/list',
        'target' => 'Cru\\Psr14EventList\\Backend\\Controller\\AdminModuleController::listCoreEventsAction',
        'action' => 'listCoreEventsAction',
    ],
    'tx_psr14_event_list_index' => [
        'path' => '/psr14/event/list/index',
        'target' => 'Cru\\Psr14EventList\\Backend\\Controller\\AdminModuleController::indexAction',
        'action' => 'index',
    ],
];
