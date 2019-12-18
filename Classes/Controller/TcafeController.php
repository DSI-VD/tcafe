<?php
namespace Vd\Tcafe\Controller;

use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Vd\Tcafe\Resolver\DataResolver;
use Vd\Tcafe\Validator\ConfigurationValidator;
use Vd\Tcafe\Validator\FileErrorConfigurationException;

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
     */
    public function initializeAction()
    {
        if($this->settings['configurationFilePath']) {
            $filePath = trim(GeneralUtility::getFileAbsFileName($this->settings['configurationFilePath']));
            if (!file_exists($filePath)) {
                throw new FileErrorConfigurationException('File does not exist');
            }
            if (!is_readable($filePath)) {
                throw new FileErrorConfigurationException('File is not readable');
            }
        } else {
            throw new FileErrorConfigurationException('ConfigurationFilePath directive is missing in your configuration');
        }

        $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $this->configuration = $fileLoader->load($this->settings['configurationFilePath']);
        $action = $this->request->getControllerActionName();
        ConfigurationValidator::validate($this->configuration, $action);
        $this->dataResolver = GeneralUtility::makeInstance(DataResolver::class);
    }

    /**
     * The list action
     */
    public function listAction()
    {
        $records = $this->dataResolver->resolve(
            $this->configuration,
            $this->request->getControllerActionName()
        );
        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'configuration' => $this->configuration
        ]);
    }

    /**
     * @param array $filterValues
     */
    public function filterAction(array $filterValues)
    {
        $records = $this->dataResolver->resolve(
            $this->configuration,
            $this->request->getControllerActionName()
        );
        $this->setTemplate();
        $this->view->assignMultiple([
            $this->fluidVariableName => $records,
            'configuration' => $this->configuration
        ]);
    }

    /**
     * Set the correct template.
     */
    protected function setTemplate()
    {
        if (isset($this->configuration[$this->request->getControllerActionName()]['fluidVariableName'])) {
            $this->fluidVariableName = $this->configuration[$this->request->getControllerActionName()]['fluidVariableName'];

            if (isset($this->configuration[$this->request->getControllerActionName()]['templateName'])) {
                $this->view = $this->objectManager->get(StandaloneView::class);
                $this->view->setFormat('html');
                $this->view->setTemplatePathAndFilename(
                    GeneralUtility::getFileAbsFileName($this->configuration[$this->request->getControllerActionName()]['templateName'])
                );
            }
        }
    }
}

