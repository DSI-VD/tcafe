<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class FieldResolution
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
     * @param string $name
     * @param string $value
     * @param array $config
     * @param array $tcaColumn
     */
    public function __construct($name, $value, array $config, array $tcaColumn)
    {
        $this->name = $name;
        $this->value = $value;
        $this->config = $config;
        if (!isset($this->config['label'])) {
            $this->config['label'] = $tcaColumn['label'];
        }
        if (strpos($this->config['label'], 'LLL:') !== false) {
            $this->config['label'] = LocalizationUtility::translate($this->config['label']);
        }
        switch ($tcaColumn['config']['type']) {
            case 'input':
                if (isset($tcaColumn['config']['renderType'])) {
                    switch ($tcaColumn['config']['renderType']) {
                        case 'inputDateTime':
                            $this->config['type'] = 'Date';
                            break;
                        default:
                            $this->config['type'] = 'Text';
                    }
                } else {
                    $this->config['type'] = 'Text';
                }
                break;
            case 'select':
            case 'inline':
            case 'group':
                if ($tcaColumn['config']['foreign_table'] === 'sys_file_reference') {
                    $this->config['type'] = 'File';
                } else {
                    $this->config['type'] = 'Relation';
                }
                break;
            default:
                $this->config['type'] = 'Text';
                break;
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
