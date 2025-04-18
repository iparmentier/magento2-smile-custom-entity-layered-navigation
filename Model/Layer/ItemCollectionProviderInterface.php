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

use Magento\Eav\Api\Data\AttributeSetInterface;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection;

/**
 * Interface \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\ItemCollectionProviderInterface
 *
 * @api
 */
interface ItemCollectionProviderInterface
{
    /**
     * @param AttributeSetInterface $entity
     * @return Collection
     */
    public function getCollection(AttributeSetInterface $entity);
}