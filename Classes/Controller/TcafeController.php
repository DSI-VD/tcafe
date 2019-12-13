<?php
namespace Vd\Tcafe\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Vd\Tcafe\Resolver\DataResolver;
use Vd\Tcafe\Utility\ConfigurationUtility;
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
     * Load and validate the configuration file.
     */
    public function initializeAction()
    {
        $fileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $this->configuration = $fileLoader->load($this->settings['configurationFilePath']);

        $action = $this->request->getControllerActionName();
        ConfigurationValidator::validate($this->configuration, $action);
        $this->dataResolver = GeneralUtility::makeInstance(DataResolver::class, $this->configuration);
    }

    public function listAction()
    {
        $rows = $this->dataResolver->resolve(
            $this->configuration['table'],
            array_keys($this->configuration[$this->request->getControllerActionName()]['fields'])
        );

        $this->view->assignMultiple([
            'rows' => $rows,
            'configuration' => $this->configuration
        ]);
    }
}

