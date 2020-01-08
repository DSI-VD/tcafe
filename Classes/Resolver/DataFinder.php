<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class DataFinder
{
    /**
     * Find the data according to the configuration.
     *
     * @param array $configuration
     * @param string $action
     * @param int $currentPage
     * @param string $additionalWhereClause
     * @param array $filterValues
     * @return array
     */
    public function find(array &$configuration, string $action, string $additionalWhereClause = '', int $currentPage = 0, array $filterValues = []): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);

        if (isset($configuration[$action]['pagination'])) {
            $configuration[$action]['pagination']['numberOfRecords'] = $queryBuilder
                ->count('uid')
                ->from($configuration['table'])
                ->execute()->fetchColumn(0);

            $queryBuilder->resetQueryParts();
            $queryBuilder
                ->setMaxResults($configuration[$action]['pagination']['itemsPerPage'])
                ->setFirstResult($currentPage * (int)$configuration[$action]['pagination']['itemsPerPage']);
            $configuration[$action]['pagination']['numberOfPages'] =
                ceil($configuration[$action]['pagination']['numberOfRecords'] / (int)$configuration[$action]['pagination']['itemsPerPage']);
        }

        foreach ($configuration[$action]['fields'] as $key => $field) {
            $queryBuilder->addSelect($key);
        }

        if (!empty($additionalWhereClause)) {
            $queryBuilder->where($additionalWhereClause);
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
                            $items = self::cleanSelectSingleItems(
                                $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['items']
                            );
                            break;
                        default:
                            $queryBuilder->andWhere(
                                $queryBuilder->expr()->eq($filter['field'], $queryBuilder->quote($filterValues[$i]))
                            );
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
                        $GLOBALS['TCA'][$configuration['table']]['columns'][$field] ?? []
                    );
                }
            }
        } else {
            $data = $rows;
        }

        return $data;
    }

    /**
     * @param array $items
     * @return array
     */
    protected static function cleanSelectSingleItems(?array $items): array
    {
        $cleanValues = [];
        foreach ($items as $item) {
            if (strpos($item[0], 'LLL:') !== false) {
                $item[0] = LocalizationUtility::translate($item[0]);
            }
            if ($item[1] !== '--div--') {
                $cleanValues[$item[1]] = $item[0];
            }
        }

        return $cleanValues;
    }
}
