<?php



use Cru\Psr14EventList\Backend\Controller\AdminModuleController;


return [
    'web_examples' => [
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
    ]
];