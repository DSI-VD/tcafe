<?php
namespace Vd\Tcafe\Finder;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Vd\Tcafe\Utility\FieldUtility;
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
     * @var bool
     */
    protected $sortable = false;

    /**
     * Map field type for fluid with values.
     *
     * @param string $name
     * @param string $value
     * @param array $config
     * @param array $tcaColumn
     * @param bool $sortable
     */
    public function __construct(
        $name,
        $value,
        array $config,
        array $tcaColumn,
        bool $sortable
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->config = $config;
        $this->sortable = $sortable;

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
                if (!empty($tcaColumn['config']['items'])) {
                    if ($tcaColumn['config']['renderType'] === 'checkboxToggle') {
                        $this->addSelectItems();
                    } else {
                        $bits = array_reverse(str_split(decbin($this->value)));
                        $items = FieldUtility::cleanCheckBoxItems($tcaColumn['config']['items']);
                        $i = 0;
                        $this->value = [];
                        foreach ($items as $item) {
                            if (!is_null($bits[$i]) && $bits[$i] == '1') {
                                $this->value[$i] = $item;
                            }
                            $i++;
                        }
                        $this->config['type'] = 'Select';
                    }
                } else {
                    $this->addSelectItems();
                }
                break;
            case 'radio':
                $this->setSelectField($tcaColumn);
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
                    $this->setSelectField($tcaColumn);
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @param bool $sortable
     */
    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    /**
     * @param array $tcaColumn
     */
    private function setSelectField(array $tcaColumn)
    {
        $this->config['type'] = 'Select';
        $items = FieldUtility::cleanSelectSingleItems($tcaColumn['config']['items']);
        $tempValue = $this->value;
        $this->value = [];
        foreach (explode(',', $tempValue) as $selectedValue) {
            if (!empty($selectedValue)) {
                $this->value[$selectedValue] = $items[$selectedValue];
            }
        }
    }

    /**
     * Add select items
     */
    private function addSelectItems()
    {
        if (!empty($this->config['items'])) {
            $tempValue = $this->value;
            $this->value = [];
            $this->value[] = $this->config['items'][$tempValue];
            $this->config['type'] = 'Select';
        } else {
            $this->config['type'] = 'Text';
        }
    }

}
