<?php
namespace Vd\Tcafe\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class FieldUtility
{
    /**
     * @param array|null $items
     * @return array
     */
    public static function cleanSelectSingleItems(?array $items): array
    {
        $cleanValues = [];
        foreach ($items as $item) {
            if (strpos($item[0], 'LLL:') !== false) {
                $item[0] = LocalizationUtility::translate($item[0]);
            }
            if ($item[1] !== '--div--') {
                $cleanValues[$item[1]] = $item[0];
            }
        }
        return $cleanValues;
    }

    /**
     * @param array|null $items
     * @return array
     */
    public static function cleanCheckBoxItems(?array $items): array
    {
        $cleanValues = [];
        foreach ($items as $item) {
            if (strpos($item[0], 'LLL:') !== false) {
                $item[0] = LocalizationUtility::translate($item[0]);
            }
            if ($item[1] !== '--div--') {
                $cleanValues[] = $item[0];
            }
        }
        return $cleanValues;
    }
}
