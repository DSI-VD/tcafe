<?php
namespace Vd\Tcafe\ViewHelpers;

use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Vd\Tcafe\Resolver\FieldResolution;

class TypolinkFileTitleViewHelper extends AbstractViewHelper
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
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $linkService = GeneralUtility::makeInstance(LinkService::class);
        $decodedTypolink = $typoLinkCodec->decode($arguments['value']);
        $linkDetails = $linkService->resolve($decodedTypolink['url']);
        /** @var File $file */
        $file = $linkDetails['file'];

        return $file->getProperty('title') !== '' ? $file->getProperty('title') : $file->getName();
    }

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'Typolink value', true);
    }
}