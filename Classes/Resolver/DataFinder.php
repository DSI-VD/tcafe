<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * @param int $currentPage
     * @param string $additionalWhereClause
     * @param array $filterValues
     * @return array
     */
    public function find(array &$configuration, string $action, string $additionalWhereClause = '', int $currentPage = 0, array $filterValues = []): array
    {
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

        // Add where clause from filters.
        if (!empty($filterValues)) {
            $filters = $configuration['list']['filters'];
            $i = 0;
            foreach ($filters as $filter) {
                if ($filterValues[$i] !== null && $filterValues[$i] !== '') {
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
                            break;
                    }
                }
                $i++;
            }
        }

        // Add the pagination.
        if (isset($configuration[$action]['pagination'])) {
            $recordsCount = $queryBuilder
                ->count('uid')
                ->from($configuration['table'])
                ->execute()->fetchColumn(0);

            $queryBuilder->resetQueryPart('select');
            $itemsPerPage = (int)$configuration[$action]['pagination']['itemsPerPage'];
            $queryBuilder
                ->setMaxResults($itemsPerPage)
                ->setFirstResult($currentPage * $itemsPerPage);

            $configuration[$action]['pagination']['numberOfPages'] = 0;
            if ($itemsPerPage !== 0) {
                $configuration[$action]['pagination']['numberOfPages'] = ceil($recordsCount / $itemsPerPage);
            }

            $configuration[$action]['pagination']['pages'] = [];
            for ($i = 0; $i < $configuration[$action]['pagination']['numberOfPages']; $i++) {
                $configuration[$action]['pagination']['pages'][] = [
                    'active' => $i == $currentPage,
                    'label' => $i + 1,
                    'index' => $i
                ];
            }
        }

        // Select fields.
        $queryBuilder->addSelect('uid');
        $queryBuilder->addSelect('pid');
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
     * @param array|null $items
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

        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($cleanValues);

        return $cleanValues;
    }
}
