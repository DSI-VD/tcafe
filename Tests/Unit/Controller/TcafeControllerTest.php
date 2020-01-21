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

    /**
     * @test
     * @dataProvider filtersProvider
     * @param $filters
     */
    public function callToFiltersDoNotReturnError($filters)
    {
        $configuration = [
            'table' => $this->table,
            'list' => [
                'filters' => $filters
            ],
            'storagePids' => '0'
        ];
        // fwrite(STDERR, print_r($filters, TRUE));
        // var_dump($configuration['list']['filters']);
        $filterFactory = FilterFactory::createAll($configuration['list']['filters'], $configuration['table'], $configuration['storagePids']);

        $this->assertTrue(is_array($filterFactory));

    }

    public function filtersProvider()
    {
        return [
            'Input Dataset' => [
                [
                    [
                        'type' => 'Input',
                        'fields' => 'title,bodytext',
                        'label' => 'Rechercher',
                        'placeholder' => 'Rechercher'
                    ]
                ]

            ],
            // @todo: make it work because actually, we have an "Invalid argument supplied for foreach()"
            // from FilterFactory::create called by FilterFactory::createAll
            'Select DataSet' => [
                [
                    [
                        'type' => 'Select',
                        'field' => 'relation_to',
                        'label' => 'Select',
                        'foreignFieldsLabel' => 'title'
                    ]
                ]
            ]
        ];
    }
}
