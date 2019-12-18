<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY,
    'Configuration/TypoScript',
    'Base Configuration'
);
