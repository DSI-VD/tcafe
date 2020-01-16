<?php
namespace Vd\Tcafe\Finder;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Vd\Tcafe\Domain\Model\Data;

class DataFinder
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @param array $configuration
     */
    public function __construct(array &$configuration)
    {
        $this->configuration = $configuration;
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->configuration['table']);
    }


    /**
     * Find the data according to the configuration.
     *
     * @param string $action
     * @param string $additionalWhereClause
     * @param int $currentPage
     * @param array $filterValues
     * @param string $sortField The sort field
     * @param string $sort The sort direction (asc/desc)
     * @return array
     */
    public function find(
        string $action,
        string $additionalWhereClause = '',
        int $currentPage = 0,
        array $filterValues = [],
        string $sortField = '',
        string $sort = ''
    ): array {
        // Add additional where clause
        if (!empty($additionalWhereClause)) {
            $this->queryBuilder->where($additionalWhereClause);
        }

        // Add storagePids clause
        if (isset($this->configuration['storagePids'])) {
            $this->queryBuilder->where(
                $this->queryBuilder->expr()->in(
                    'pid',
                    explode(',', $this->configuration['storagePids'])
                )
            );
        }

        // Populate filters
        $this->applyFilters($filterValues, $action);
        $this->paginate($action, $currentPage);
        $this->sort($sortField, $sort);
        $this->selectFields($action);

        // Execute the query.
        $data = [];
        $rows = $this->queryBuilder
            ->from($this->configuration['table'])
            ->execute()
            ->fetchAll();

        if (!isset($this->configuration[$action]['fluidVariableName'])) {
            foreach ($rows as $key => $row) {
                foreach ($row as $field => $value) {
                    $data[$key][$field] = new Data(
                        $field,
                        $value,
                        $this->configuration[$action]['fields'][$field] ?? [],
                        $GLOBALS['TCA'][$this->configuration['table']]['columns'][$field] ?? [],
                        $this->isSortable($action, $field)
                    );
                }
            }
        } else {
            $data = $rows;
        }

        return $data;
    }

    /**
     * @param array $filterValues
     * @param string $action
     */
    protected function applyFilters(array $filterValues, string $action)
    {
        $filters = $this->configuration['list']['filters'];
        if (!empty($filterValues)) {
            $i = 0;
            foreach ($filters as $filter) {
                if ($filterValues[$i] !== null && $filterValues[$i] !== '') {
                    switch ($filter['type']) {
                        case 'Input':
                            $clauses = '';
                            foreach (explode(',', $filter['fields']) as $field) {
                                $clauses .=
                                    $this->queryBuilder->expr()->like($field,
                                        $this->queryBuilder->quote('%' . $filterValues[$i] . '%')) . ' OR ';
                            }
                            $this->queryBuilder->andWhere(substr($clauses, 0, -4));
                            break;
                        case 'Select':
                            $foreignTable = $GLOBALS['TCA'][$this->configuration['table']]['columns'][$filter['field']]['config']['foreign_table'];
                            if (!isset($foreignTable)) {
                                $this->queryBuilder->andWhere(
                                    $this->queryBuilder->expr()->eq($filter['field'],
                                        $this->queryBuilder->quote($filterValues[$i]))
                                );
                            } else {
                                $joinTable = $GLOBALS['TCA'][$this->configuration['table']]['columns'][$filter['field']]['config']['MM'];
                                if (!isset($joinTable)) {
                                    $this->queryBuilder->andWhere(
                                        $this->queryBuilder->expr()->orX(
                                            $this->queryBuilder->expr()->eq($filter['field'], $filterValues[$i]),
                                            $this->queryBuilder->expr()->like($filter['field'], $this->queryBuilder->expr()->literal($filterValues[$i] . ',%')),
                                            $this->queryBuilder->expr()->like($filter['field'], $this->queryBuilder->expr()->literal('%,' . $filterValues[$i] . ',%')),
                                            $this->queryBuilder->expr()->like($filter['field'], $this->queryBuilder->expr()->literal('%,' . $filterValues[$i]))
                                        )
                                    );
                                } else {
                                    $this->queryBuilder->leftJoin(
                                        $this->configuration['table'],
                                        $joinTable,
                                        'mmTable',
                                        $this->queryBuilder->expr()->eq(
                                            'mmTable.uid_local',
                                            $this->queryBuilder->quoteIdentifier($this->configuration['table'] . '.uid')
                                        )
                                    )->andWhere(
                                        $this->queryBuilder->expr()->eq('mmTable.uid_foreign', $filterValues[$i])
                                    );
                                    if ($joinTable === 'sys_category_record_mm') {
                                        $this->queryBuilder->andWhere(
                                            $this->queryBuilder->expr()->eq('mmTable.tablenames',
                                                $this->queryBuilder->quote($this->configuration['table'])),
                                            $this->queryBuilder->expr()->eq('mmTable.fieldname',
                                                $this->queryBuilder->quote($filter['field']))
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
    }

    /**
     * @param string $action
     */
    protected function selectFields(string $action)
    {
        if ($this->configuration[$action]['fields']['pid']) {
            $this->configuration[$action]['fields']['pid']['hidden'] = false;
        } else {
            $this->queryBuilder->addSelect('pid');
            $this->configuration[$action]['fields']['pid']['hidden'] = true;
        }
        if ($this->configuration[$action]['fields']['uid']) {
            $this->configuration[$action]['fields']['uid']['hidden'] = false;
        } else {
            $this->queryBuilder->addSelect('uid');
            $this->configuration[$action]['fields']['uid']['hidden'] = true;
        }
        foreach ($this->configuration[$action]['fields'] as $key => $field) {
            $this->queryBuilder->addSelect($key);
        }
    }

    /**
     * Isolate the sort direction
     *
     * @param string $sortField
     * @param string $sort
     */
    protected function sort(string $sortField, string $sort)
    {
        $orderBy = [];
        $defaultSortDir = isset($confSorting['order']) ?? 'ASC';
        $sortDirection = !empty($sort) ? $sort : $defaultSortDir;
        if (!empty($sortField)) {
            $orderBy = [
                'sortField' => $sortField,
                'sort' => strtoupper($sortDirection)
            ];
        }

        if (count($orderBy) > 0 && !empty($orderBy['sortField'])) {
            $this->queryBuilder->orderBy($orderBy['sortField'], $orderBy['sort']);
        }
    }

    /**
     * @param string $action
     * @param int $currentPage
     */
    protected function paginate(string $action, int $currentPage)
    {
        if ($this->configuration[$action]['pagination']) {
            $itemsPerPage = (int)$this->configuration[$action]['pagination']['itemsPerPage'];
            $this->queryBuilder
                ->setMaxResults($itemsPerPage)
                ->setFirstResult($currentPage * $itemsPerPage);
        }
    }

    /**
     * Set the field as sortable or not
     *
     * @param string $action
     * @param string $fieldName
     * @return bool
     */
    protected function isSortable(string $action, string $fieldName)
    {
        $sortable = false;
        $confSortableFields = $this->configuration[$action]['sorting']['sortableFields'];

        // List of fields that require a sort flag
        if (is_array($confSortableFields)) {
            if (in_array($fieldName, $confSortableFields)) {
                $sortable = true;
            }
        } else {
            // All fields are sortable?
            $matched = preg_match('/^\*$/', $confSortableFields, $match, PREG_OFFSET_CAPTURE);
            $sortable = $matched[0][1] === 1 ? true : false;
        }

        return $sortable;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
