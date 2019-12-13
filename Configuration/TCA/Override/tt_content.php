<?php

/***************
 * Plugin
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    \Vd\TcaFe\Utility\ConfigurationUtility::EXT_KEY,
    'Pi1',
    'TCA for the frontend'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][\Vd\TcaFe\Utility\ConfigurationUtility::PI1] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][\Vd\TcaFe\Utility\ConfigurationUtility::PI1] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(\Vd\TcaFe\Utility\ConfigurationUtility::PI1, 'FILE:EXT:news/Configuration/FlexForms/flexform_news.xml');
