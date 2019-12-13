<?php
namespace Vd\Tcafe\Resolver;


use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class FieldResolution
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @param string $name
     * @param string $value
     * @param array $configuration
     * @param array $tcaColumn
     */
    public function __construct($name, $value, array $configuration, array $tcaColumn)
    {
        $this->name = $name;
        $this->value = $value;
        $this->configuration = $configuration;
        switch ($tcaColumn['config']['type']) {
            case 'input':
                if (isset($tcaColumn['config']['renderType'])) {
                    switch ($tcaColumn['config']['renderType']) {
                        case 'inputDateTime':
                            $this->type = 'Date';
                            break;
                        default:
                            $this->type = 'Text';
                    }
                } else {
                    $this->type = 'Text';
                }
                break;
            case 'select':
                $this->type = 'Text';
                break;
            default:
                $this->type = 'Text';
                break;
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
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
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }
}
