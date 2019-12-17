<?php
namespace Vd\Tcafe\Validator;

class ConfigurationValidator
{
    const IGNORE_FIELDS = [
        'uid',
        'pid',
        't3ver_id',
        't3ver_oid',
        't3ver_wsid',
        't3ver_label',
        't3ver_state',
        't3ver_count',
        't3ver_stage',
        't3ver_tstamp',
        'perms_userid',
        'perms_groupid',
        'perms_user',
        'perms_group',
        'perms_everybody'
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
            if (!in_array($key,
                    self::IGNORE_FIELDS) && !isset($GLOBALS['TCA'][$configuration['table']]['columns'][$key])) {
                throw new UnexistingColumnException('The column ' . $key . ' set in the configuration file does not exist.');
            }
        }
    }
}
