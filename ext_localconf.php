<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function ($extensionKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Vd.' . \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY,
        'Pi1',
        [
            'Tcafe' => 'filter,list,show'
        ],
        [
            'Tcafe' => 'filter'
        ]
    );
}, \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY);
