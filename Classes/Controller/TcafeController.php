<?php
namespace Vd\Tcafe\Controller;

use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
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

