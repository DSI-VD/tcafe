<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterResolver
{
    /**
     * @param array $configuration
     * @param string $action
     * @param string $clauses
     * @return array
     */
    public function resolve(array $configuration, string $action, string $clauses = ''): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);


        foreach ($configuration[$action]['fieldsets'] as $fieldset => $filters) {
            foreach ($filters as $key => $filter) {
                foreach ($filter['fields'] as $field => $v) {

                    $queryBuilder->addSelect($v);
                }

            }

        }

        return [];
    }
}
