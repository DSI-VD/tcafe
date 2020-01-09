<?php
namespace Vd\Tcafe\Finder;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Vd\Tcafe\Validator\ConfigurationValidator;

class Field
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Map field type for fluid with values.
     *
     * @param string $name
     * @param string $value
     * @param array $config
     * @param array $tcaColumn
     */
    public function __construct(
        $name,
        $value,
        array $config,
        array $tcaColumn
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->config = $config;

        // Set the label
        if (!isset($this->config['label'])) {
            $this->config['label'] = $tcaColumn['label'];
        }
        if (strpos($this->config['label'], 'LLL:') !== false) {
            $this->config['label'] = LocalizationUtility::translate($this->config['label']);
        }

        // Set the type
        switch ($tcaColumn['config']['type']) {
            case 'text':
            case 'slug':
            case 'input':
                if (isset($tcaColumn['config']['renderType'])) {
                    switch ($tcaColumn['config']['renderType']) {
                        case 'inputDateTime':
                            $this->config['type'] = 'Date';
                            break;
                        case 'inputLink':
                            $this->config['type'] = 'Link';
                            break;
                        default:
                            $this->config['type'] = 'Text';
                            break;
                    }
                } else {
                    if ($tcaColumn['config']['enableRichtext']) {
                        $this->config['type'] = 'RichText';
                    } else {
                        $this->config['type'] = 'Text';
                    }
                }
                break;
            case 'check':
            case 'radio':
                $this->config['type'] = 'MultiValue';
                if (!isset($this->config['values'])) {
                    $this->config['values'] = $this->cleanMultiValues($tcaColumn['config']['items']);
                }
                break;
            case 'inline':
                if ($tcaColumn['config']['foreign_table'] === 'sys_file_reference') {
                    $this->config['type'] = 'File';
                } else {
                    $this->config['type'] = 'Relation';
                }
                break;
            case 'select':
                if (!isset($tcaColumn['config']['foreign_table'])) {
                    $this->config['type'] = 'MultiValue';
                    $this->config['values'] = $this->cleanMultiValues($tcaColumn['config']['items']);
                } else {
                    $this->config['type'] = 'Relation';
                }
                break;
            case 'group':
                $this->config['type'] = 'Relation';
                break;
            default:
                break;
        }


        // Add default types
        if (array_key_exists($this->name, ConfigurationValidator::UNDEFINED_FIELDS_IN_TCA)) {
            ArrayUtility::mergeRecursiveWithOverrule($this->config, ConfigurationValidator::UNDEFINED_FIELDS_IN_TCA[$this->name]);
        }
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return (string)$this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}