<?php
namespace Vd\Tcafe\Controller;

use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Vd\Tcafe\Resolver\DataResolver;
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
     */
    public function initializeAction()
    {
        $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $this->configuration = $fileLoader->load($this->settings['configurationFilePath']);
        $action = $this->request->getControllerActionName();
        ConfigurationValidator::validate($this->configuration, $action);
        $this->dataResolver = GeneralUtility::makeInstance(DataResolver::class);

        if (isset($this->configuration[$this->request->getControllerActionName()]['fluidVariableName'])) {
            $this->fluidVariableName = $this->configuration[$this->request->getControllerActionName()]['fluidVariableName'];
        }
    }

    public function listAction()
    {
        $rows = $this->dataResolver->resolve(
            $this->configuration,
            $this->request->getControllerActionName()
        );

        $this->view->assignMultiple([
            $this->fluidVariableName => $rows,
            'configuration' => $this->configuration
        ]);
    }
}

