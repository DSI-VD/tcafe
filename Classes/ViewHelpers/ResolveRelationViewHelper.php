<?php
namespace Vd\Tcafe\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Vd\Tcafe\Resolver\DataResolver;
use Vd\Tcafe\Resolver\FieldResolution;

class ResolveRelationViewHelper extends AbstractViewHelper
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

        // $foreignTable = $GLOBALS['TCA'][$arguments['table']]['columns'][$field->getName()]['config']['foreign_table'];

        /** @var DataResolver $dataResolver */
        $dataResolver = GeneralUtility::makeInstance(DataResolver::class);
        //$rows = $dataResolver->resolve($config, 'list', $queryBuilder->expr()->in('uid', $field->getValue()));
        $rows = $dataResolver->resolveFields(
            $arguments['table'],
            $arguments['field'],
            '',
            $arguments['uidLocal']
        );

        // inject to fluid
        $variableProvider->add('records', $rows);
        $content = $renderChildrenClosure();
        $variableProvider->remove('records');

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
        $this->registerArgument('field', FieldResolution::class, '', true);
        $this->registerArgument('table', 'string', '', true);
    }
}
