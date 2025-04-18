<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 *
 * @category  Amadeco
 * @package   Amadeco_SmileCustomEntityLayeredNavigation
 * @copyright Copyright (c) Amadeco (https://www.amadeco.fr) - Ilan Parmentier
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Smile\ScopedEav\Model\AbstractEntity;

/**
 * Source model for Sort By options
 */
class SortBy implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            //['value' => AbstractEntity::ATTRIBUTE_SET_ID, 'label' => __('ID')],
            //['value' => AbstractEntity::UPDATED_AT, 'label' => __('Updated At')],
            ['value' => AbstractEntity::CREATED_AT, 'label' => __('New')],
            ['value' => AbstractEntity::NAME, 'label' => __('Name')]
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
            //AbstractEntity::ATTRIBUTE_SET_ID => __('ID'),
            //AbstractEntity::UPDATED_AT => __('Updated At'),
            AbstractEntity::CREATED_AT => __('New'),
            AbstractEntity::NAME => __('Name')
        ];
    }
}