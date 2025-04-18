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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;

/**
 * Abstract Filter Model for Custom Entity Layered Navigation
 */
abstract class AbstractFilter extends DataObject implements FilterInterface
{
    const ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS = 1;

    /**
     * Request variable name with filter value
     *
     * @var string
     */
    protected $_requestVar;

    /**
     * Array of filter items
     *
     * @var array
     */
    protected $_items;

    /**
     * Filter item factory
     *
     * @var ItemFactory
     */
    protected $_filterItemFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog layer
     *
     * @var Layer
     */
    protected $_entityLayer;

    /**
     * Item Data Builder
     *
     * @var DataBuilder
     */
    protected $itemDataBuilder;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        $this->_filterItemFactory = $filterItemFactory;
        $this->_storeManager = $storeManager;
        $this->_entityLayer = $layer;
        $this->itemDataBuilder = $itemDataBuilder;
        parent::__construct($data);
        if ($this->hasAttributeModel()) {
            $this->_requestVar = $this->getAttributeModel()->getAttributeCode();
        }
    }

    /**
     * Get filter items count
     *
     * @return int
     */
    public function getItemsCount(): int
    {
        return count($this->getItems());
    }

    /**
     * Get filter items
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        if ($this->_items === null) {
            $this->_initItems();
        }

        return $this->_items;
    }

    /**
     * Set all filter items
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->_items = $items;
        return $this;
    }

    /**
     * Get filter request var
     *
     * @return string
     */
    public function getRequestVar(): string
    {
        return $this->_requestVar;
    }

    /**
     * Set request variable name
     *
     * @param string $varName
     * @return $this
     */
    public function setRequestVar($varName)
    {
        $this->_requestVar = $varName;
        return $this;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue()
    {
        return null;
    }

    /**
     * Retrieve filter value for Clear All Items filter state
     *
     * @return mixed
     */
    public function getCleanValue()
    {
        return null;
    }

    /**
     * Apply filter to collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        return $this;
    }

    /**
     * Get layer model
     *
     * @return Layer
     */
    public function getLayer(): Layer
    {
        return $this->_entityLayer;
    }

    /**
     * Initialize filter items
     *
     * @return  \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        $data = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
        }
        $this->_items = $items;
        return $this;
    }

    /**
     * Get data array for building filter items
     *
     * Result array should have next structure:
     * array(
     *      $index => array(
     *          'label' => $label,
     *          'value' => $value,
     *          'count' => $count
     *      )
     * )
     *
     * @return array
     */
    protected function _getItemsData()
    {
        return [];
    }

    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected function _createItem($label, $value, $count = 0)
    {
        return $this->_filterItemFactory->create()
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count);
    }

    /**
     * Get all product ids from collection with applied filters
     *
     * @return array
     */
    protected function _getFilterEntityIds(): array
    {
        return $this->getLayer()->getEntityCollection()->getAllIdsCache();
    }

    /**
     * Get product collection select object with applied filters
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getBaseCollectionSql(): \Magento\Framework\DB\Select
    {
        return $this->getLayer()->getEntityCollection()->getSelect();
    }

    /**
     * Set attribute model to filter
     *
     * @param \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface $attribute
     * @return $this
     */
    public function setAttributeModel($attribute): self
    {
        $this->setRequestVar($attribute->getAttributeCode());
        $this->setData('attribute_model', $attribute);

        return $this;
    }

    /**
     * Get attribute model associated with filter
     *
     * @return \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface
     * @throws LocalizedException
     */
    public function getAttributeModel()
    {
        if (!$this->hasData('attribute_model')) {
            throw new LocalizedException(__('The attribute model is not defined'));
        }

        return $this->getData('attribute_model');
    }

    /**
     * Get filter text label
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getName()
    {
        return $this->getAttributeModel()->getStoreLabel();
    }

    /**
     * Retrieve current store id scope
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_getData('store_id');
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Set store id scope
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * Retrieve Website ID scope
     *
     * @return int
     */
    public function getWebsiteId()
    {
        $websiteId = $this->_getData('website_id');
        if ($websiteId === null) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        }
        return $websiteId;
    }

    /**
     * Set Website ID scope
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData('website_id', $websiteId);
    }

    /**
     * Clear current element link text, for example 'Clear Price'
     *
     * @return false|string
     */
    public function getClearLinkText()
    {
        return false;
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return  string|bool
     */
    protected function getOptionText($optionId)
    {
        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
    }

    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return int
     */
    protected function getAttributeIsFilterable($attribute)
    {
        return (int)$attribute->getIsFilterable();
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     * @return bool
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        return $optionCount < $totalSize;
    }
}