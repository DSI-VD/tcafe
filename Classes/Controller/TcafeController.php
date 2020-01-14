<?php
namespace Vd\Tcafe\Controller;

use Symfony\Component\Yaml\Exception\ParseException;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Fluid\View\StandaloneView;
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
     * @var string
     */
    protected $sort = '';

    /**
     * @var string
     */
    protected $sortField = '';

    /**
     * Load and validate the configuration file.
     *
     * @throws ConfigurationFileException
     * @throws \Exception
     */
    public function initializeAction()
    {
        $this->dataFinder = GeneralUtility::makeInstance(DataFinder::class, $this->uriBuilder);
        try {
            $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
            $this->configuration = $fileLoader->load($this->settings['configurationFilePath']);
            $this->action = $this->request->getControllerActionName();
            $this->sortField = $this->configuration[$this->action]['sorting']['field'] ?? '';
            $this->sort = $this->configuration[$this->action]['sorting']['sort'] ?? 'ASC';

            ConfigurationValidator::validate($this->configuration, $this->action == 'filter' ? 'list' : $this->action, $this->settings);
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
     */
    public function listAction(int $currentPage = 0)
    {
        $records = $this->dataFinder->find(
            $this->configuration,
            $this->action,
            '',
            $currentPage
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
            $this->configuration,
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

        if($this->request->hasArgument('sort')) {
            $this->sort = trim($this->request->getArgument('sort'));
        }

        if($this->request->hasArgument('sortField')) {
            $this->sortField = trim($this->request->getArgument('sortField'));
        }

        // Add CSS framework for icons (sorting)
        if($this->settings['libIconUrl']) {
            $GLOBALS['TSFE']->additionalHeaderData['lib_icon'] = '<link rel="stylesheet" type="text/css" href="' . $this->settings['libIconUrl'] . '" media="all">';
        }

        $records = $this->dataFinder->find(
            $this->configuration,
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
            'filters' => $this->configuration['list']['filters'],
            'currentPage' => $currentPage,
            'configuration' => $this->configuration,
            'filterValues' => $filterValues,
            'sort' => strtolower($this->sort),
            'sortField' => $this->sortField

        ]);
    }

    /**
     * Set the correct template.
     */
    protected function setTemplate()
    {
        if (isset($this->configuration[$this->request->getControllerActionName()]['fluidVariableName'])) {
            $this->fluidVariableName = $this->configuration[$this->request->getControllerActionName()]['fluidVariableName'] ?? $this->fluidVariableName;

            if (isset($this->configuration[$this->request->getControllerActionName()]['templateName'])) {
                $partialRootPaths = $this->view->getPartialRootPaths();
                $this->view = $this->objectManager->get(StandaloneView::class);
                $this->view->setFormat('html');
                $this->view->setTemplatePathAndFilename(
                    GeneralUtility::getFileAbsFileName($this->configuration[$this->request->getControllerActionName()]['templateName'])
                );
                $this->view->setPartialRootPaths($partialRootPaths);
            }
        }
    }
}

