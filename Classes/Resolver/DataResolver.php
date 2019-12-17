<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class DataResolver
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $table
     * @param array $fields
     * @param string $whereClause
     * @return array
     */
    public function resolve(string $table, array $fields, string $whereClause = ''): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        foreach ($fields as $field) {
            $queryBuilder->addSelect($field);
        }

        $statement = $queryBuilder
            ->from($table)
            ->execute();

        $data = [];
        $rows = $statement->fetchAll();

        foreach ($rows as $key => $row) {
            foreach ($row as $field => $value)
            $data[$key][] = new FieldResolution(
                $field,
                $value,
                $this->configuration['list']['fields'][$field],
                $GLOBALS['TCA'][$table]['columns'][$field]
            );
        }

        return $data;
    }
}
