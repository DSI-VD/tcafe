<?php
namespace Vd\Tcafe\Utility;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class TcafeController extends ActionController
{
    public function listAction()
    {
        DebuggerUtility::var_dump($this->settings);
        die;
    }
}

