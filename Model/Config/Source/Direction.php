<?php
/**
 * Amadeco SmileCustomEntityLayeredNavigation module
 *
 * @category  Amadeco
 * @package   Amadeco_SmileCustomEntityLayeredNavigation
 * @copyright Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Config\Source;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Option\ArrayInterface;

/**
 * Source model for sort direction options
 */
class Direction implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => SortOrder::SORT_DESC, 'label' => __('Descending')],
            ['value' => SortOrder::SORT_ASC, 'label' => __('Ascending')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public static function toArray(): array
    {
        return [
            SortOrder::SORT_DESC => __('Descending'),
            SortOrder::SORT_ASC => __('Ascending')
        ];
    }
}