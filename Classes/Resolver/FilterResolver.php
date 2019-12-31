<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterResolver
{
    protected $configuration = [];

    /**
     * FilterResolver constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getClauses(): string
    {
        return '';
    }
}
