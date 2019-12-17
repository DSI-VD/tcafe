<?php
namespace Vd\Tcafe\Controller;

use TYPO3\CMS\Core\Utility\ArrayUtility;
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

        $tcafeOrg = ['tcafe' =>''];
        $tcafeOrg['tcafe'] = $fileLoader->load($this->settings['configurationFilePath']);
        $tableName = $tcafeOrg['tcafe']['tableName'];
        $tcafe[$tableName]['tcafe'] = $tcafeOrg['tcafe'];

        ArrayUtility::mergeRecursiveWithOverrule(
            $tcafe[$tableName]['tcafe'],
            $GLOBALS['TCA'][$tableName]
        );
        /*DebuggerUtility::var_dump($tcafe[$tableName]);
        DebuggerUtility::var_dump($GLOBALS['TCA'][$tableName]['columns']);*/

        foreach ( $tcafeOrg['tcafe']['actions'] as $k=>$v){

            if(is_array($tcafe[$tableName]['tcafe']['actions'][$k]['columns'])){

                ArrayUtility::mergeRecursiveWithOverrule(
                    $tcafe[$tableName]['tcafe']['actions'][$k]['columns'],
                    $GLOBALS['TCA'][$tableName]['columns']
                );
                ArrayUtility::mergeRecursiveWithOverrule(
                    $tcafe[$tableName]['tcafe']['actions'][$k]['columns'],
                    $tcafeOrg['tcafe']['actions'][$k]['columns']
                );
            }

            // DebuggerUtility::var_dump($GLOBALS['TCA'][$tableName]['columns']);
            //DebuggerUtility::var_dump($k);

           DebuggerUtility::var_dump($tcafe[$tableName]['tcafe']['actions'][$k]['columns']);
        }



        DebuggerUtility::var_dump($tcafe[$tableName]['tcafe']);



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

