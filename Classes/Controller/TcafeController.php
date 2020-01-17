<?php
namespace Vd\Tcafe\Controller;

use Symfony\Component\Yaml\Exception\ParseException;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Vd\Tcafe\Domain\Model\Pagination;
use Vd\Tcafe\Factory\FilterFactory;
use Vd\Tcafe\Finder\DataFinder;
use Vd\Tcafe\Validator\ConfigurationFileException;
use Vd\Tcafe\Validator\ConfigurationValidator;

class TcafeController extends ActionController
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var DataFinder
     */
    protected $dataFinder = null;

    /**
     * @var string
     */
    protected $fluidVariableName = 'rows';

    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var string|null
     */
    protected $sort = null;

    /**
     * @var string|null
     */
    protected $sortField = null;

    /**
     * Load and validate the configuration file.
     *
     * @throws ConfigurationFileException
     * @throws \Exception
     */
    public function initializeAction()
    {
        try {
            $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
            $this->configuration = $fileLoader->load($this->settings['configurationFilePath'], YamlFileLoader::PROCESS_PLACEHOLDERS);
            $this->dataFinder = GeneralUtility::makeInstance(DataFinder::class, $this->configuration);
            $this->action = $this->request->getControllerActionName() == 'filter' ? 'list' : $this->request->getControllerActionName();
//            $this->sortField = $this->configuration[$this->action]['sorting']['field'] ?? '';
//            $this->sort = $this->configuration[$this->action]['sorting']['sort'] ?? 'ASC';
            ConfigurationValidator::validate($this->configuration, $this->action, $this->settings);
        } catch (ParseException | \RuntimeException $e) {
            throw new ConfigurationFileException('The was a problem loading the configuration file ' . $this->settings['configurationFilePath'] . ' : ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * The list action.
     *
     * @param int $currentPage
     * @throws NoSuchArgumentException
     */
    public function listAction(int $currentPage = 0)
    {
        $this->setSorting();
        $records = $this->dataFinder->find(
            $this->action,
            '',
            $currentPage,
            [],
            $this->sortField,
            $this->sort
        );

        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'currentPage' => $currentPage,
            'configuration' => $this->configuration,
            'action' => $this->action
        ]);
    }

    /**
     * The detail action.
     *
     * @param int $uid
     */
    public function detailAction(int $uid)
    {
        $records = $this->dataFinder->find(
            'detail',
            'uid=' . $uid
        );

        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'currentPid' => $GLOBALS['TSFE']->id,
            'configuration' => $this->configuration
        ]);
    }

    /**
     * The filter action.
     *
     * @param array $filterValues
     * @param int $currentPage
     * @throws NoSuchArgumentException
     */
    public function filterAction(array $filterValues = [], int $currentPage = 0)
    {
        $this->setSorting();
        if($this->settings['libIconUrl']) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addCssLibrary($this->settings['libIconUrl']);
        }

        $records = $this->dataFinder->find(
            'list',
            '',
            $currentPage,
            $filterValues,
            $this->sortField,
            $this->sort
        );

        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'filters' => FilterFactory::createAll($this->configuration['list']['filters'], $this->configuration['table'], $this->configuration['storagePids']),
            'currentPage' => $currentPage,
            'configuration' => $this->configuration,
            'pagination' => new Pagination($this->configuration['list']['pagination'], $this->configuration['table'], $currentPage, $this->dataFinder->getQueryBuilder()),
            'filterValues' => $filterValues,
            'sort' => strtolower($this->sort),
            'sortField' => $this->sortField
        ]);
    }

    /**
     * @throws NoSuchArgumentException
     */
    protected function setSorting(): void
    {
        if ($this->request->hasArgument('sort')) {
            $this->sort = trim($this->request->getArgument('sort'));
        }

        if ($this->request->hasArgument('sortField')) {
            $this->sortField = trim($this->request->getArgument('sortField'));
        }
    }

    /**
     * If fluidVariableName is set in configuration file we need to change the view to a StandaloneView.
     */
    protected function setTemplate(): void
    {
        if (isset($this->configuration[$this->action]['fluidVariableName'])) {
            $this->fluidVariableName = $this->configuration[$this->action]['fluidVariableName'] ?? $this->fluidVariableName;
            if (isset($this->configuration[$this->action]['templateName'])) {
                $partialRootPaths = $this->view->getPartialRootPaths();
                $this->view = $this->objectManager->get(StandaloneView::class);
                $this->view->setControllerContext($this->controllerContext);
                $this->view->setFormat('html');
                $this->view->setTemplatePathAndFilename(
                    GeneralUtility::getFileAbsFileName($this->configuration[$this->action]['templateName'])
                );
                $this->view->setPartialRootPaths($partialRootPaths);
            }
        }
    }
}

