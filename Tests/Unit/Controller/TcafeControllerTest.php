<?php
namespace Vd\Tcafe\Tests\Unit\Controller;

use Vd\Tcafe\Factory\FilterFactory;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class TcafeControllerTest extends UnitTestCase
{

    /**
     * @var string
     */
    protected $table = 'tx_tcafe_record';

    /** @test */
    public function callToFiltersDoNotReturnError()
    {
        $configuration = [
            'table' => $this->table,
            'list' => [
                'filters' => [
                    [
                        'type' => 'Input',
                        'fields' => 'title,bodytext',
                        'label' => 'Rechercher',
                        'placeholder' => 'Rechercher'
                    ]
                ]
            ],
            'storagePids' => '0'
        ];

        $filterFactory = FilterFactory::createAll($configuration['list']['filters'], $configuration['table'], $configuration['storagePids']);

        $this->assertTrue(is_array($filterFactory));

    }
}
