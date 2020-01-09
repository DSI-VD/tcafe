<?php
namespace Vd\Tcafe\Provider;

use Faker\Generator;
use WIND\Randomdata\Service\RandomdataService;
use WIND\Randomdata\Provider\ProviderInterface;

class RandomMultiValueProvider implements ProviderInterface
{

    /**
     * Generate
     *
     * @param Generator $faker
     * @param array $configuration
     * @param RandomdataService $randomDataService
     * @param array $previousFieldsData
     * @return mixed
     */
    static public function generate(
        Generator $faker,
        array $configuration,
        RandomdataService $randomDataService,
        array $previousFieldsData
    ) {
        $configuration = array_merge([
            'minimum' => 1,
            'maximum' => 3
        ], $configuration);

        $count = rand($configuration['minimum'], $configuration['maximum']);

        $dataArr = [];
        if($configuration['values'] && is_array($configuration['values'])) {
            for($i = 0; $i < $count; $i++) {
                $dataArr[] = $configuration['values'][$i];
            }
        }

        return implode(',', $dataArr);
    }
}
