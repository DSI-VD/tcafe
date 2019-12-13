<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function ($extensionKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'TcaFe.' . \Vd\TcaFe\Utility\ConfigurationUtility::EXT_KEY,
        'Pi1',
        [
            'TcaFe' => 'list,show'
        ],
        []
    );
}, \Vd\TcaFe\Utility\ConfigurationUtility::EXT_KEY);
