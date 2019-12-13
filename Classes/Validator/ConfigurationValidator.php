<?php
namespace Vd\Tcafe\Validator;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ConfigurationValidator
{

    /**
     * @param array $configuration
     * @param string $action
     * @throws UnexistingColumnException
     * @throws UnexistingTableException
     */
    public static function validate(array $configuration, string $action)
    {
        if (!isset($GLOBALS['TCA'][$configuration['table']])) {
            throw new UnexistingTableException('The table ' . $configuration['table'] . ' does not exist.');
        }
        foreach ($configuration[$action]['fields'] as $key => $field) {
            if(!isset($GLOBALS['TCA'][$configuration['table']]['columns'][$key])) {
                throw new UnexistingColumnException('The column ' . $key . ' set in the configuration file does not exist.');
            }
        }
    }
}
