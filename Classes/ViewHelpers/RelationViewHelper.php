<?php
namespace Vd\Tcafe\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Vd\Tcafe\Resolver\DataResolver;
use Vd\Tcafe\Resolver\FieldResolution;

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
        /** @var FieldResolution $field */
        $field = $arguments['field'];
        $foreignTable = $GLOBALS['TCA'][$arguments['table']]['columns'][$arguments['foreignFieldName']]['config']['foreign_table'];

        $config = [
            'table' => $foreignTable,
            'list' => [
                'fields' => $arguments['foreignTableSelectFields']
            ]
        ];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($arguments['table']);
        $dataResolver = GeneralUtility::makeInstance(DataResolver::class);
        $rows = $dataResolver->resolve($config, 'list', $queryBuilder->expr()->in('uid', $arguments['foreignFieldValue']));

        $variableProvider->add($arguments['as'], $rows);
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