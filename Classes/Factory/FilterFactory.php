<?php
namespace Vd\Tcafe\Factory;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Vd\Tcafe\Domain\Model\Filter;
use Vd\Tcafe\Utility\FieldUtility;

class FilterFactory
{
    /**
     * @param array $config
     * @param string $table
     * @param string $storagePids
     * @return Filter
     */
    public static function create(array $config, string $table, string $storagePids = ''): Filter
    {
        return new Filter($config, self::fetchFilterItems($config, $table, $storagePids));
    }

    /**
     * @param array|null $filtersConf
     * @param string $table
     * @param string $storagePids
     * @return array|null
     */
    public static function createAll(?array $filtersConf, string $table, string $storagePids): ?array
    {
        if ($filtersConf) {
            $filters = [];
            foreach ($filtersConf as $filterConf) {
                if (in_array($filterConf['type'], ['Select', 'Radio', 'Checkbox'])) {
                    $filters[] = self::create($filterConf, $table, $storagePids);
                } else {
                    $filters[] = self::create($filterConf, $table);
                }
            }

            return $filters;
        }

        return null;
    }

    /**
     * @param array $config
     * @param string $table
     * @param string $storagePids
     * @return array
     */
    protected static function fetchFilterItems(array $config, string $table, string $storagePids): array
    {
        $items = [];
        $foreignTable = $GLOBALS['TCA'][$table]['columns'][$config['field']]['config']['foreign_table'];
        if (isset($config['defaultSelectLabel'])) {
            $items[''] = $config['defaultSelectLabel'];
        } else {
            $items[''] = '';
        }
        if (!isset($foreignTable)) {
            $items += FieldUtility::cleanSelectSingleItems(
                $GLOBALS['TCA'][$table]['columns'][$config['field']]['config']['items']
            );
        } else {
            $queryBuilderForeign = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($foreignTable);
            $queryBuilderLocal = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $joinTable = $GLOBALS['TCA'][$table]['columns'][$config['field']]['config']['MM'];
            if (!isset($joinTable)) {
                foreach (explode(',', $config['foreignFieldsLabel']) as $field) {
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
                                ->from($table)
                                ->where(
                                    $queryBuilderLocal->expr()->in('pid', $storagePids)
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
}
