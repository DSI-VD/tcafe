<?php
namespace Vd\Tcafe\Finder;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Vd\Tcafe\Utility\FieldUtility;

class DataFinder
{
    /**
     * @var UriBuilder
     */
    protected $uriBuilder = null;

    /**
     * @param UriBuilder $uriBuilder
     */
    public function __construct(UriBuilder $uriBuilder = null)
    {
        $this->uriBuilder = $uriBuilder;
    }

    /**
     * Find the data according to the configuration.
     *
     * @param array $configuration
     * @param string $action
     * @param string $additionalWhereClause
     * @param int $currentPage
     * @param array $filterValues
     * @param string $sortField The sort field
     * @param string $sort The sort direction (asc/desc)
     * @return array
     */
    public function find(
        array &$configuration,
        string $action,
        string $additionalWhereClause = '',
        int $currentPage = 0,
        array $filterValues = [],
        string $sortField = '',
        string $sort = ''
    ): array {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);

        // Add additional where clause.
        if (!empty($additionalWhereClause)) {
            $queryBuilder->where($additionalWhereClause);
        }

        // Add storagePids clause
        if (isset($configuration['storagePids'])) {
            $queryBuilder->where($queryBuilder->expr()->in('pid', explode(',', $configuration['storagePids'])));
        }

        // Populate filters.
        $filters = $configuration['list']['filters'];
        if (!is_null($filters)) {
            $i = 0;
            foreach ($filters as $filter) {
                if (in_array($filter['type'], ['Select', 'Radio', 'Checkbox'])) {
                    $configuration[$action]['filters'][$i]['items'] = $this->populateInput($filter, $configuration);
                }
                $i++;
            }
        }

        // Add where clause from filters.
        if (!empty($filterValues)) {
            $i = 0;
            foreach ($filters as $filter) {
                if ($filterValues[$i] !== null && $filterValues[$i] !== '') {
                    switch ($filter['type']) {
                        case 'Input':
                            $clauses = '';
                            foreach (explode(',', $filter['fields']) as $field) {
                                $clauses .=
                                    $queryBuilder->expr()->like($field,
                                        $queryBuilder->quote('%' . $filterValues[$i] . '%')) . ' OR ';
                            }
                            $queryBuilder->andWhere(substr($clauses, 0, -4));
                            break;
                        case 'Select':
                            $foreignTable = $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['foreign_table'];
                            if (!isset($foreignTable)) {
                                $queryBuilder->andWhere(
                                    $queryBuilder->expr()->eq($filter['field'], $queryBuilder->quote($filterValues[$i]))
                                );
                            } else {
                                $joinTable = $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['MM'];
                                if (!isset($joinTable)) {
                                    $queryBuilder->andWhere(
                                        $queryBuilder->expr()->orX(
                                            $queryBuilder->expr()->eq($filter['field'], $filterValues[$i]),
                                            $queryBuilder->expr()->like($filter['field'], $queryBuilder->expr()->literal($filterValues[$i] . ',%')),
                                            $queryBuilder->expr()->like($filter['field'], $queryBuilder->expr()->literal('%,' . $filterValues[$i] . ',%')),
                                            $queryBuilder->expr()->like($filter['field'], $queryBuilder->expr()->literal('%,' . $filterValues[$i]))
                                        )
                                    );
                                } else {
                                    $queryBuilder->leftJoin(
                                        $configuration['table'],
                                        $joinTable,
                                        'mmTable',
                                        $queryBuilder->expr()->eq(
                                            'mmTable.uid_local',
                                            $queryBuilder->quoteIdentifier($configuration['table'] . '.uid')
                                        )
                                    )->andWhere(
                                        $queryBuilder->expr()->eq('mmTable.uid_foreign', $filterValues[$i])
                                    );
                                    if ($joinTable === 'sys_category_record_mm') {
                                        $queryBuilder->andWhere(
                                            $queryBuilder->expr()->eq('mmTable.tablenames', $queryBuilder->quote($configuration['table'])),
                                            $queryBuilder->expr()->eq('mmTable.fieldname', $queryBuilder->quote($filter['field']))
                                        );
                                    }
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
                $i++;
            }
        }

        // Add the pagination.
        if (isset($configuration[$action]['pagination'])) {
            $queryBuilderCounter = clone($queryBuilder);
            $recordsCount = $queryBuilderCounter
                ->count('uid')
                ->from($configuration['table'])
                ->execute()
                ->fetchColumn(0);

            $itemsPerPage = (int)$configuration[$action]['pagination']['itemsPerPage'];
            $queryBuilder
                ->setMaxResults($itemsPerPage)
                ->setFirstResult($currentPage * $itemsPerPage);
            $numberOfPages = 0;
            if ($itemsPerPage !== 0) {
                $numberOfPages = ceil($recordsCount / $itemsPerPage);
            }

            // Data needed to build the pagination
            $maximumNumberOfLinks = $configuration[$action]['pagination']['maximumNumberOfLinks'];
            if ($maximumNumberOfLinks > $numberOfPages) {
                $maximumNumberOfLinks = $numberOfPages;
            }

            $delta = floor($maximumNumberOfLinks / 2);
            $displayRangeStart = $currentPage - $delta;
            $displayRangeEnd = $currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);

            if ($displayRangeStart < 1) {
                $displayRangeEnd -= $displayRangeStart - 1;
            }

            if ($displayRangeEnd > $numberOfPages) {
                $displayRangeStart -= $displayRangeEnd - $numberOfPages;
            }

            $displayRangeStart = (int)max($displayRangeStart, 1);
            $displayRangeEnd = (int)min($displayRangeEnd, $numberOfPages);

            $configuration[$action]['pagination']['numberOfPages'] = $numberOfPages;
            $configuration[$action]['pagination']['displayRangeStart'] = $displayRangeStart - 1;
            $configuration[$action]['pagination']['displayRangeEnd'] = $displayRangeEnd + 1;
            $configuration[$action]['pagination']['hasLessPages'] = $displayRangeStart > 2;
            $configuration[$action]['pagination']['hasMorePages'] = $displayRangeEnd + 1 < $numberOfPages;
            $configuration[$action]['pagination']['lastPage'] = $numberOfPages - 1;
            $configuration[$action]['pagination']['nextPage'] = $currentPage + 1;
            $configuration[$action]['pagination']['previousPage'] = $currentPage - 1;

            $configuration[$action]['pagination']['pages'] = [];
            for ($i = 0; $i < $configuration[$action]['pagination']['numberOfPages']; $i++) {
                $configuration[$action]['pagination']['pages'][] = [
                    'active' => $i == $currentPage,
                    'label' => $i + 1,
                    'index' => $i
                ];
            }
        }

        // Implement sorting
        $orderBy = $this->checkSorting($sortField, $sort);
        if(count($orderBy) > 0 && !empty($orderBy['sortField'])) {
            $queryBuilder->orderBy($orderBy['sortField'], $orderBy['sort']);
        }

        // Select fields.
        if (array_key_exists('pid', $configuration[$action]['fields'])) {
            $configuration[$action]['fields']['pid']['hidden'] = false;
        } else {
            $queryBuilder->addSelect('pid');
            $configuration[$action]['fields']['pid']['hidden'] = true;
        }
        if (array_key_exists('uid', $configuration[$action]['fields'])) {
            $configuration[$action]['fields']['uid']['hidden'] = false;
        } else {
            $queryBuilder->addSelect('uid');
            $configuration[$action]['fields']['uid']['hidden'] = true;
        }
        foreach ($configuration[$action]['fields'] as $key => $field) {
            $queryBuilder->addSelect($key);
        }

        // Execute the query.
        $data = [];

        $rows = $queryBuilder
            ->from($configuration['table'])
            ->execute()
            ->fetchAll();

        if (!isset($configuration[$action]['fluidVariableName'])) {
            foreach ($rows as $key => $row) {
                foreach ($row as $field => $value) {

                    $data[$key][$field] = new Field(
                        $field,
                        $value,
                        $configuration[$action]['fields'][$field] ?? [],
                        $GLOBALS['TCA'][$configuration['table']]['columns'][$field] ?? [],
                        $this->isSortable($configuration[$action]['sorting'], $field)
                    );
                }
            }
        } else {
            $data = $rows;
        }

        return $data;
    }

    /**
     * @param array $filter
     * @param array $configuration
     * @return array
     */
    protected function populateInput(array $filter, array $configuration)
    {
        $items = [];
        $foreignTable = $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['foreign_table'];
        if (isset($filter['defaultSelectLabel'])) {
            $items[''] = $filter['defaultSelectLabel'];
        } else {
            $items[''] = '';
        }
        if (!isset($foreignTable)) {
            $items += FieldUtility::cleanSelectSingleItems(
                $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['items']
            );
        } else {
            $queryBuilderForeign = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($foreignTable);
            $queryBuilderLocal = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);
            $joinTable = $GLOBALS['TCA'][$configuration['table']]['columns'][$filter['field']]['config']['MM'];
            if (!isset($joinTable)) {
                foreach (explode(',', $filter['foreignFieldsLabel']) as $field) {
                    $queryBuilderForeign->addSelect($field);
                }
                $rows = $queryBuilderForeign
                    ->from($foreignTable)
                    ->addSelect('uid', 'pid')
                    ->execute()
                    ->fetchAll();
            } else {
                $rows = $queryBuilderForeign
                    ->select('uid', 'title')
                    ->from($foreignTable)
                    ->join(
                        $foreignTable,
                        $joinTable,
                        'mmTable',
                        $queryBuilderForeign->expr()->eq(
                            'mmTable.uid_foreign',
                            $queryBuilderForeign->quoteIdentifier($foreignTable . '.uid')
                        )
                    )
                    ->where(
                        $queryBuilderForeign->expr()->in(
                            'mmTable.uid_local',
                            $queryBuilderLocal->select('uid')
                                ->from($configuration['table'])
                                ->where(
                                    $queryBuilderLocal->expr()->in('pid', $configuration['storagePids'])
                                )->getSQL()
                        )
                    )
                    ->groupBy('uid')
                    ->execute()
                    ->fetchAll();
            }

            foreach ($rows as $entry) {
                $items[$entry['uid']] = $entry['title'];
            }
        }

        return $items;
    }

    /**
     * Isolate the sort direction
     *
     * @param string $sortField
     * @param string $sort
     * @return array
     */
    public function checkSorting($sortField, $sort)
    {
        $orderBy = [];
        $defaultSortDir = isset($confSorting['order']) ?? 'ASC'; // Use configuration or set ASC as default
        $sortDirection = !empty($sort) ? $sort : $defaultSortDir; // Use default or GET parameter direction
        if(!empty($sortField)) {
            $orderBy = [
                'sortField' => $sortField, // GET param or Default (can be empty)
                'sort' => strtoupper($sortDirection)
            ];

        }

        return $orderBy;
    }

    /**
     * Set the field as sortable or not
     *
     * @param $conf
     * @param $fieldName
     * @return bool
     */
    public function isSortable($conf, $fieldName)
    {
        $sortable = false;
        $confSortableFields = $conf['sortableFields'];

        // List of fields that require a sort flag
        if(is_array($confSortableFields)) {
            if(in_array($fieldName, $confSortableFields)) {
                $sortable = true;
            }
        } else { // All fields are sortable?
            $matched = preg_match('/^\*$/', $confSortableFields, $match, PREG_OFFSET_CAPTURE);
            $sortable = $matched[0][1] === 1 ? true : false;
        }

        return $sortable;
    }
}
