<?php
namespace Vd\Tcafe\Resolver;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Vd\Tcafe\Validator\ConfigurationValidator;

class DataResolver
{
    /**
     * @param array $configuration
     * @param string $action
     * @return array
     */
    public function resolve(array $configuration, string $action): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($configuration['table']);

        foreach ($configuration[$action]['fields'] as $key => $field) {
            $queryBuilder->addSelect($key);
        }

        $queryBuilder->addSelect('uid');
        $queryBuilder->addSelect('pid');

        $statement = $queryBuilder
            ->from($configuration['table'])
            ->execute();

        $data = [];
        $rows = $statement->fetchAll();
        foreach ($rows as $key => $row) {
            foreach ($row as $field => $value) {

                // Set visible to true for fields that are missing a key with visible value
                $fieldConf = $configuration[$action]['fields'][$field];
                if(is_array($fieldConf) && !array_key_exists('visible', $fieldConf)) {
                    $configuration[$action]['fields'][$field]['visible'] = true;
                }

                if (in_array($field,
                        ConfigurationValidator::IGNORE_FIELDS) && $configuration[$action]['fields'][$field]) {
                    $configuration[$action]['fields'][$field]['visible'] = false;
                }

                $data[$key][$field] = new FieldResolution(
                    $field,
                    $value,
                    $configuration[$action]['fields'][$field] ?? [],
                    $GLOBALS['TCA'][$configuration['table']]['columns'][$field] ?? []
                );
            }
        }

        return $data;
    }
}
