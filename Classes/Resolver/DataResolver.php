<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataResolver
{
    /**
     * Resolve tcafe.table.actions with clauses
     * @param array $configuration
     * @param string $action
     * @param string $clauses
     * @return array
     */
    public function resolve(array $configuration, string $action, string $clauses = ''): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);
        foreach ($configuration[$action]['fields'] as $key => $field) {
            $queryBuilder->addSelect($key);
        }
        if (!empty($clauses)) {
            $queryBuilder->where($clauses);
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

    /**
     * Get Foreign relations for FieldResolution
     * @param string $localTable
     * @param FieldResolution $field
     * @param string $clauses
     * @param int $localUid
     * @return FieldResolution[]
     */
    public function resolveFields(
        string $localTable,
        FieldResolution $field,
        string $clauses = '',
        int $localUid = null
    ): array {

        $tableLocal = $field->getTableLocal ?? $localTable;

        // case select and group relation
        $referenceIndex = GeneralUtility::makeInstance(ReferenceIndex::class);
        $relationsRecords = $referenceIndex->getRelations($tableLocal,
            ['uid' => $localUid, $field->getName() => $field->getValue()]);

        // other case


        // use $relationsRecords
        $data = [];
        $selectFields = [];
        $relatedRows = [];
        foreach ($field->getConfig()['fields'] as $key => $v) {
            $selectFields[] = $key;
        }

        foreach ($relationsRecords[$field->getName()]['itemArray'] as $relation) { // uid tablename
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($relation['table']);
            foreach ($selectFields as $k => $v) {
                $queryBuilder->addSelect($v);
            }
            if (!empty($clauses)) {
                $queryBuilder->where($clauses);
            }
            $queryBuilder->addSelect('uid');
            $queryBuilder->addSelect('pid');
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('uid', $relation['id'])
            );
            $statement = $queryBuilder
                ->from($relation['table'])
                ->execute();

            $row = $statement->fetch();

            // $newArr = array_map(function($item){ return $item['email'];}, $rows);
            $row['_table_'] = $relation['table'];

            $relatedRows[] = $row;
        }

        foreach ($relatedRows as $key => $row) {
            foreach ($row as $fieldK => $value) {
                $data[$key][$fieldK] = new FieldResolution(
                    $fieldK,
                    $value,
                    $fieldsConfig[$fieldK] ?? [],
                    $GLOBALS['TCA'][$table = $row['_table_']]['columns'][$fieldK] ?? [],
                    $row['_table_']
                );
                unset($data[$key]['_table_']);
            }
        }
        return $data;
    }

}
