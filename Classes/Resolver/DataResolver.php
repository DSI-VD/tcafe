<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataResolver
{
    /**
     * Find the data according to the configuration.
     *
     * @param array $configuration
     * @param string $action
     * @param string $clauses
     * @param array $filterValues
     * @return array
     */
    public function resolve(array $configuration, string $action, string $clauses = '', array $filterValues = []): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);

        foreach ($configuration[$action]['fields'] as $key => $field) {
            $queryBuilder->addSelect($key);
        }

        if (!empty($clauses)) {
            $queryBuilder->where($clauses);
        }

        $filters = $configuration['list']['filters'];
        $i = 0;
        if (!empty($filterValues)) {
            foreach ($filters as $filter) {
                if ($filterValues[$i] !== '') {
                    switch ($filter['type']) {
                        case 'Input':
                            foreach (explode(',', $filter['fields']) as $field) {
                                $queryBuilder->orWhere(
                                    $queryBuilder->expr()->like($field, $queryBuilder->quote('%' . $filterValues[$i] . '%'))
                                );
                            }
                            break;
                        case 'Select':
                            $queryBuilder->andWhere(
                                $queryBuilder->expr()->eq($filter['field'], $queryBuilder->quote($filterValues[$i]))
                            );
                            break;
                        default:
                            break;
                    }
                }
                $i++;
            }
        }

        $queryBuilder->addSelect('uid');
        $queryBuilder->addSelect('pid');

        $statement = $queryBuilder
            ->from($configuration['table'])
            ->execute();

        $data = [];
        $rows = $statement->fetchAll();

        if (!isset($configuration[$action]['fluidVariableName'])) {
            foreach ($rows as $key => $row) {
                foreach ($row as $field => $value) {
                    $data[$key][$field] = new FieldResolution(
                        $field,
                        $value,
                        $configuration[$action]['fields'][$field] ?? [],
                        $GLOBALS['TCA'][$configuration['table']]['columns'][$field] ?? [],
                        $configuration['table'],
                        $configuration[$action]['linkedFields'] ?? []
                    );
                }
            }
        } else {
            $data = $rows;
        }

        return $data;
    }
}
