<?php
namespace Vd\Tcafe\Domain\Model;

class Filter
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     * @param array $items
     */
    public function __construct(array $config, array $items)
    {
        $this->config = $config;
        $this->config['items'] = $items;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
