<?php
namespace Vd\Tcafe\Controller;

use Symfony\Component\Yaml\Exception\ParseException;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Vd\Tcafe\Resolver\DataResolver;
use Vd\Tcafe\Validator\ConfigurationFileException;
use Vd\Tcafe\Validator\ConfigurationValidator;

class TcafeController extends ActionController
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var DataResolver
     */
    private $dataResolver = null;

    /**
     * @var string
     */
    private $fluidVariableName = 'rows';

    /**
     * Load and validate the configuration file.
     *
     * @throws ConfigurationFileException
     * @throws \Exception
     */
    public function initializeAction()
    {
        $this->dataResolver = GeneralUtility::makeInstance(DataResolver::class);
        try {
            $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
            $this->configuration = $fileLoader->load($this->settings['configurationFilePath']);
            $action = $this->request->getControllerActionName();
            ConfigurationValidator::validate($this->configuration, $action, $this->settings);
        } catch (ParseException | \RuntimeException $e) {
            throw new ConfigurationFileException('The was a problem loading the configuration file ' . $this->settings['configurationFilePath'] . ' : ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * The list action.
     */
    public function listAction()
    {
        $records = $this->dataResolver->resolve(
            $this->configuration,
            'list'
        );

        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'configuration' => $this->configuration
        ]);
    }

    /**
     * The detail action.
     *
     * @param int $uid
     */
    public function detailAction(int $uid)
    {
        $records = $this->dataResolver->resolve(
            $this->configuration,
            'detail',
            'uid=' . $uid
        );

        $this->view->assignMultiple([
            'currentPid' => $GLOBALS['TSFE']->id,
            $this->fluidVariableName => $records,
            'configuration' => $this->configuration
        ]);
    }

    /**
     * The filter action.
     *
     * @param array $filterValues
     */
    public function filterAction(array $filterValues = [])
    {
        $records = $this->dataResolver->resolve(
            $this->configuration,
            'list',
            '',
            $filterValues
        );

        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'filters' => $this->configuration['list']['filters'],
            'configuration' => $this->configuration,
            'filterValues' => $filterValues,
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

