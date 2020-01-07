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
            ['DB values', '--div--'],
        ],
        'foreign_table' => 'tx_styleguide_staticdata',
        'foreign_table_where' => 'AND {#tx_styleguide_staticdata}.{#value_1} LIKE \'%foo%\' ORDER BY uid',
        'foreign_table_prefix' => 'A prefix: ',
    ],
];
