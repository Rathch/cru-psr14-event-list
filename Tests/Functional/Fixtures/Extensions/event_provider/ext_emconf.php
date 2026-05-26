<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Event provider fixture',
    'description' => 'Provides real event classes for functional tests.',
    'category' => 'misc',
    'state' => 'stable',
    'author' => 'cru/psr14-event-list',
    'author_email' => '',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '12.4.2-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
