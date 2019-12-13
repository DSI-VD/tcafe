<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'tcafe',
    'description' => 'Extension to display any database table data in frontend.',
    'category' => 'backend',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99'
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'author' => 'Etat de Vaud - DGNSI',
    'author_email' => 'support.typo3@vd.ch',
    'author_company' => 'Etat de Vaud - DGNSI',
    'version' => '0.0.1-dev',
];
