<?php
namespace Vd\Tcafe\Tests\Unit\ViewHelpers;

use Vd\Tcafe\Domain\Model\Data;
use Vd\Tcafe\ViewHelpers\StrPosViewHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;

/**
 * This ViewHelper requires two arguments: haystack and needle
 *
 * Class StrPosViewHelperTest
 * @package Vd\Tcafe\Tests\Unit\ViewHelpers
 */
class StrPosViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var RenderChildrenViewHelper|MockObject
     */
    protected $viewHelper;

    /**
     * @var string
     */
    protected $extension = 'tcafe';

    /**
     * @var string
     */
    protected $table = 'tx_tcafe_record';

    protected function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getMockBuilder(StrPosViewHelper::class)
            ->setMethods(['initializeArguments'])
            ->getMock();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    protected function init()
    {
        if(!ExtensionManagementUtility::isLoaded($this->extension)) {
            $this->expectExceptionMessage('The required extension to test in not loaded');
        }
    }

    /**
     * @test
     */
    public function setArgumentsUnderTestCanBeCalled()
    {

        $this->init();
        $expected = false;
        $dataOrigin = new Data(
            'url',
            '',
            [
                'label' => 'Label URL',
                'type' => 'link'
            ],
            [],
            false
        );

        // Pass static arguments to the GetDataFromRowViewHelper
        $this->setArgumentsUnderTest(
            $this->viewHelper,
            [
                'haystack' => $dataOrigin->getValue(),
                'needle' => 't3://file'
            ]
        );

        // Compare expected and returned value from ViewHelper
        $this->assertSame($expected, $this->viewHelper->render());
    }

}
