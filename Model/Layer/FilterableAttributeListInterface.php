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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;

use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;

/**
 * Interface \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterableAttributeListInterface
 *
 * @api
 */
interface FilterableAttributeListInterface
{
    /**
     * Retrieve list of filterable attributes
     *
     * @param Layer|null $layer The layer requesting the attributes.
     *
     * @return CustomEntityAttributeInterface[]
     */
    public function getList(?Layer $layer = null): array;

    /**
     * Get all indexable attributes regardless of layer context
     *
     * @return CustomEntityAttributeInterface[]
     */
    public function getAllIndexableAttributes(): array;
}