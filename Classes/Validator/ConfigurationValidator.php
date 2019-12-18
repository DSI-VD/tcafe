<?php
namespace Vd\Tcafe\Validator;

class ConfigurationValidator
{
    const IGNORE_FIELDS = [
        'uid' => ['type' => 'Text'],
        'pid' => ['type' => 'Text'],
        't3ver_id' => ['type' => 'Text'],
        't3ver_oid' => ['type' => 'Text'],
        't3ver_wsid' => ['type' => 'Text'],
        't3ver_label' => ['type' => 'Text'],
        't3ver_state' => ['type' => 'Text'],
        't3ver_count' => ['type' => 'Text'],
        't3ver_stage' => ['type' => 'Text'],
        't3ver_tstamp' => ['type' => 'Date'],
        'perms_userid' => ['type' => 'Text'],
        'perms_groupid' => ['type' => 'Text'],
        'perms_user' => ['type' => 'Text'],
        'perms_group' => ['type' => 'Text'],
        'perms_everybody' => ['type' => 'Text'],
        'tstamp' => ['type' => 'Date'],
        'crdate' => ['type' => 'Date'],
        'cruser_id' => ['type' => 'Text'],
        'delete' => ['type' => 'Text']
    ];

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
            if (!array_key_exists($key, self::IGNORE_FIELDS) &&
                !isset($GLOBALS['TCA'][$configuration['table']]['columns'][$key])
            ) {
                throw new UnexistingColumnException('The column ' . $key . ' set in the configuration file does not exist.');
            }
        }
    }
}
