<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'psr 14 eventlist',
    'description' => 'Lists all psr 14 events',
    'category' => 'be',
    'state' => 'stable',
    'author' => 'Christian Rath-Ulrich',
    'author_email' => 'christian@rath-ulrich.de',
    'author_company' => '',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '12.4.2-13.9.99',
            'backend' => '12.4.2-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
