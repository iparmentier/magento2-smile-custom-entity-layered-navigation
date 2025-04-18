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

/**
 * Constructor modification point for Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer.
 *
 * All such context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with their
 * corresponding abstract classes.
 */
interface ContextInterface
{
    /**
     * @return ItemCollectionProviderInterface
     */
    public function getCollectionProvider();

    /**
     * @return StateKeyInterface
     */
    public function getStateKey();

    /**
     * @return CollectionFilterInterface
     */
    public function getCollectionFilter();
}