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
     * @param array $settings
     * @throws UnknownColumnException
     * @throws UnknownTableException
     */
    public static function validate(array $configuration, string $action, array $settings)
    {
        if (!isset($GLOBALS['TCA'][$configuration['table']])) {
            throw new UnknownTableException('The table ' . $configuration['table'] . ' does not exist.');
        }

        foreach ($configuration[$action]['fields'] as $key => $field) {
            if (!array_key_exists($key, self::IGNORE_FIELDS) &&
                !isset($GLOBALS['TCA'][$configuration['table']]['columns'][$key])
            ) {
                throw new UnknownColumnException('The column ' . $key . ' set in ' . $settings['configurationFilePath'] . ' does not exist.');
            }
        }
    }
}
