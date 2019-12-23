<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Error\DebugExceptionHandler;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Vd\Tcafe\Validator\ConfigurationValidator;

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



        foreach ($configuration[$action]['fieldsets']as $fieldset => $filters) {
            foreach ($filters as $key => $filter) {
                foreach ($filter['fields'] as $field => $v) {

                    $queryBuilder->addSelect($v);
                }

            }

        }

        return [];
    }
}
