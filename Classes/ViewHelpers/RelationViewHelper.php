<?php
namespace Vd\Tcafe\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Vd\Tcafe\Finder\DataFinder;

class RelationViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $variableProvider = $renderingContext->getVariableProvider();
        $foreignTable = $GLOBALS['TCA'][$arguments['table']]['columns'][$arguments['foreignFieldName']]['config']['foreign_table'];
        $newConfiguration = [
            'table' => $foreignTable,
            'list' => [
                'fields' => $arguments['foreignTableSelectFields']
            ]
        ];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($arguments['table']);
        $dataFinder = GeneralUtility::makeInstance(DataFinder::class);
        $rows = [];
        if (!empty($arguments['foreignFieldValue'])) {
            $uidValues = $arguments['foreignFieldValue'];

            if($foreignTable === 'sys_category') {
                $mmTable = $GLOBALS['TCA'][$arguments['table']]['columns'][$arguments['foreignFieldName']]['config']['MM'];
                $mmMatchFieldFields = $GLOBALS['TCA'][$arguments['table']]['columns'][$arguments['foreignFieldName']]['config']['MM_match_fields'];
                $selectFields = [];
                $requiredForeignFields = $newConfiguration['list']['fields'];
                // @todo: debug multiple fields for foreign table in YAML file
                foreach ($requiredForeignFields as $key => $value) {
                    $selectFields[] = $foreignTable . '.' . $key;
                }

                $queryBuilderRelation = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
                    ->getQueryBuilderForTable($foreignTable);
                // $statement = $queryBuilderRelation->select(implode(',', $selectFields))->from('sys_category')
                $statement = $queryBuilderRelation->select('uid')->from($foreignTable)
                    ->join(
                        $foreignTable,
                        $mmTable,
                        'mm',
                        $queryBuilderRelation->expr()->andX(
                            $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->quoteIdentifier($foreignTable . '.uid')),
                            $queryBuilder->expr()->in('mm.uid_foreign', $arguments['uidLocal']),
                            $queryBuilder->expr()->eq('mm.tablenames', $queryBuilder->quote($mmMatchFieldFields['tablenames'])),
                            $queryBuilder->expr()->eq('mm.fieldname', $queryBuilder->quote($mmMatchFieldFields['fieldname']))
                        )
                    );
                    $catRows = $statement->execute()->fetchAll();

                    $newArr = [];
                    array_walk_recursive($catRows, function($item) use (&$newArr) { $newArr[] = $item; });

                    if(count($newArr) > 0) {
                        $uidValues = implode(',', $newArr);
                    }
            }

            $rows = $dataFinder->find(
                $newConfiguration,
                'list',
                $queryBuilder->expr()->in('uid', $uidValues)
            );
        }

        $variableProvider->add($arguments['as'], $rows);
        $variableProvider->add('newConfiguration', $newConfiguration);
        $content = $renderChildrenClosure();
        $variableProvider->remove($arguments['as']);

        return $content;
    }

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('uidLocal', 'string', '', true);
        $this->registerArgument('foreignFieldName', 'string', '', true);
        $this->registerArgument('foreignFieldValue', 'string', '', true);
        $this->registerArgument('foreignTableSelectFields', 'string', '', true);
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('as', 'string', '', false, 'records');
    }
}
