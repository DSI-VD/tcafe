<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY,
    \Vd\Tcafe\Utility\ConfigurationUtility::PI1_NAME,
    'TCA for the frontend'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][\Vd\Tcafe\Utility\ConfigurationUtility::PI1_KEY] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][\Vd\Tcafe\Utility\ConfigurationUtility::PI1_KEY] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    \Vd\Tcafe\Utility\ConfigurationUtility::PI1_KEY,
    'FILE:EXT:tcafe/Configuration/FlexForms/flexform_tcafe.xml'
);
