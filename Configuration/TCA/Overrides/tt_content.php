<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    \Vd\Tcafe\Utility\ConfigurationUtility::EXT_KEY,
    'Pi1',
    'TCA for the frontend'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][\Vd\Tcafe\Utility\ConfigurationUtility::PI1] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][\Vd\Tcafe\Utility\ConfigurationUtility::PI1] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    \Vd\Tcafe\Utility\ConfigurationUtility::PI1,
    'FILE:EXT:tcafe/Configuration/FlexForms/flexform_tcafe.xml'
);
