<?php

$GLOBALS['TCA']['fe_users']['columns']['select_single_3'] = [
    'label' => 'select_single_3 static values, dividers, foreign_table_where',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Static values', '--div--'],
            ['static -2', -2],
            ['static -1', -1],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'select_single_3', '', 'after:username');

