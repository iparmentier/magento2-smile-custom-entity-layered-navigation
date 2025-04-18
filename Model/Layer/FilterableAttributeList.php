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

use Magento\Eav\Model\Entity\Attribute\Source\Table as TableSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\CustomEntity\Attribute\Collection;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\CustomEntity\Attribute\CollectionFactory as CustomEntityAttributeCollectionFactory;

/**
 * Provides list of filterable attributes for the layer context.
 */
class FilterableAttributeList implements FilterableAttributeListInterface
{
    /**
     * @var CustomEntityAttributeCollectionFactory
     */
    private CustomEntityAttributeCollectionFactory $attributeCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param CustomEntityAttributeCollectionFactory $attributeCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomEntityAttributeCollectionFactory $attributeCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get filterable attributes based on frontend input type for the given layer's attribute set.
     *
     * @param Layer|null $layer Layer instance or null
     * @return CustomEntityAttributeInterface[]
     * @throws LocalizedException
     */
    public function getList(?Layer $layer = null): array
    {
        /** @var Collection $collection */
        $collection = $this->attributeCollectionFactory->create();
        $collection->setItemObjectClass(\Smile\CustomEntity\Model\CustomEntity\Attribute::class);

        if ($layer instanceof Layer) {
            $attributeSet = $layer->getCurrentAttributeSet();
            if ($attributeSet && $attributeSet->getAttributeSetId()) {
                $attributeSetId = $attributeSet->getAttributeSetId();
                $collection->setAttributeSetFilter($attributeSetId);
            }
        }

        $collection = $this->_prepareAttributeCollection($collection);
        $collection->setOrder('position', 'ASC');
        $collection->load();

        $items = [];
        foreach ($collection->getItems() as $item) {
            // Ensure multiselect attributes have a source model
            if ($item->getFrontendInput() == 'multiselect' && !$item->getData('source_model')) {
                $item->setData('source_model', TableSource::class);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get all indexable attributes regardless of the layer context.
     *
     * @return CustomEntityAttributeInterface[]
     * @throws LocalizedException
     */
    public function getAllIndexableAttributes(): array
    {
        return $this->getList();
    }

    /**
     * Add filters to attribute collection
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function _prepareAttributeCollection(Collection $collection): Collection
    {
        $collection->addIsFilterableFilter();
        return $collection;
    }
}