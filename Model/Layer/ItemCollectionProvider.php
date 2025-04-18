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
use Magento\Store\Model\StoreManagerInterface;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection as CustomEntityCollection;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\CollectionFactory as CustomEntityCollectionFactory;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\ItemCollectionProviderInterface;

class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
    * @var StoreManagerInterface
    */
    private StoreManagerInterface $storeManager;

    /**
     * @var CustomEntityCollectionFactory
     */
    private CustomEntityCollectionFactory $entityCollectionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomEntityCollectionFactory $entityCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomEntityCollectionFactory $entityCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->entityCollectionFactory = $entityCollectionFactory;
    }

    /**
     * @param AttributeSetInterface $entity
     * @return Collection
     */
    public function getCollection(AttributeSetInterface $entity)
    {
        $collection = $this->entityCollectionFactory->create();
        $collection->addAttributeToFilter('attribute_set_id', $entity->getAttributeSetId());
        $collection->addIsActiveFilter();

        return $collection;
    }

    /**
     * Return store id.
     *
     * If store id is underfined for category return current active store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return (int)$this->_getData('store_id');
        }
        return (int)$this->storeManager->getStore()->getId();
    }
}