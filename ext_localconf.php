<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function ($extensionKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Vd.' . $extensionKey,
        \Vd\Tcafe\Utility\ConfigurationUtility::PI1_NAME,
        [
            'Tcafe' => 'filter,list,detail,json'
        ],
        [
            'Tcafe' => 'filter,json'
        ]
    );
}, \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY);
