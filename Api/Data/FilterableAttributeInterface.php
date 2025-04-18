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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Api\Data;

use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;

/**
 * Interface for Custom Entity attributes with filterable property
 */
interface FilterableAttributeInterface extends CustomEntityAttributeInterface
{
    /**
     * Constants for filterable values
     */
    public const NOT_FILTERABLE = 0;
    public const FILTERABLE_WITH_RESULTS = 1;
    public const FILTERABLE_NO_RESULTS = 2;

    /**
     * Constants for attribute keys
     */
    public const IS_FILTERABLE = 'is_filterable';
    public const POSITION = 'position';

    /**
     * Get if attribute is filterable in layered navigation
     *
     * @return int
     */
    public function getIsFilterable(): int;

    /**
     * Set if attribute is filterable in layered navigation
     *
     * @param int $isFilterable
     * @return $this
     */
    public function setIsFilterable(int $isFilterable);

    /**
     * Get position in layered navigation
     *
     * @return int
     */
    public function getPosition(): int;

    /**
     * Set position in layered navigation
     *
     * @param int $position
     * @return $this
     */
    public function setPosition(int $position);
}