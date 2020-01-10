<?php

$GLOBALS['TCA']['tx_news_domain_model_news']['columns']['select_single_3'] = [
    'label' => 'select_single_3 static values',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Static values', '--div--'],
            ['Toto', 1],
            ['Tata', 2],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'select_single_3', '', 'after:title');
