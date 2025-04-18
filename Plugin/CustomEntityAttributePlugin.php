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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Plugin;

use Smile\CustomEntity\Model\CustomEntity\Attribute;
use Amadeco\SmileCustomEntityLayeredNavigation\Api\Data\FilterableAttributeInterface;

/**
 * Plugin to add isFilterable and position functionality to custom entity attributes
 */
class CustomEntityAttributePlugin
{
    /**
     * Add getIsFilterable and getPosition methods
     *
     * @param Attribute $subject
     * @param callable $proceed
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function aroundCall(Attribute $subject, callable $proceed, $method, $args)
    {
        switch ($method) {
            case 'getIsFilterable':
                return $subject->getData(FilterableAttributeInterface::IS_FILTERABLE) ?
                    (int)$subject->getData(FilterableAttributeInterface::IS_FILTERABLE) :
                    FilterableAttributeInterface::NOT_FILTERABLE;
            case 'setIsFilterable':
                $subject->setData(FilterableAttributeInterface::IS_FILTERABLE,
                    isset($args[0]) ? (int)$args[0] : FilterableAttributeInterface::NOT_FILTERABLE
                );
                return $subject;
            case 'getPosition':
                return $subject->getData(FilterableAttributeInterface::POSITION) ?
                    (int)$subject->getData(FilterableAttributeInterface::POSITION) :
                    0;
            case 'setPosition':
                $subject->setData(FilterableAttributeInterface::POSITION, isset($args[0]) ? (int)$args[0] : 0);
                return $subject;
            default:
                return $proceed($method, $args);
        }
    }
}