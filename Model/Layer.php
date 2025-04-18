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
 * @copyright Copyright (c) Amadeco (https://www.amadeco.fr)
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model;

use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection as CustomEntityCollection;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\CollectionFactory as CustomEntityCollectionFactory;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\ContextInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Config\Source\SortBy;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterableAttributeList;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterList;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\AbstractFilter;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\State;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\StateFactory;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\Layer as LayerResource;

/**
 * Custom Entity Layer Model
 *
 * Core class for the layered navigation system - manages:
 * - Collection of entities to be displayed
 * - Current state of active filters
 * - Available filters based on current attribute set
 */
class Layer extends DataObject implements ResetAfterRequestInterface
{
    /**
     * Registry key for current attribute set
     */
    public const REGISTRY_KEY_ATTRIBUTE_SET = 'current_attribute_set';

    /**
     * Registry key for current layer
     */
    public const LAYER_CUSTOM_ENTITY = 'custom_entity';

    /**
     * Key which can be used for load/save aggregation data
     *
     * @var string
     */
    protected $_stateKey = null;

    /**
     * Custom entity set collections array
     *
     * @var array
     */
    private array $_entityCollections = [];

    /**
     * @var array
     */
    private array $filterableAttributes = [];

    /**
     * @param ContextInterface $context
     * @param StateFactory $_layerStateFactory
     * @param FilterableAttributeList $filterableAttributeList
     * @param FilterList $filterList
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param CustomEntityCollectionFactory $entityCollectionFactory
     * @param RequestInterface $request
     * @param FilterableAttributeList $filterableAttributeList
     * @param array $data
     */
    public function __construct(
        private readonly ContextInterface $context,
        private readonly StateFactory $_layerStateFactory,
        private readonly FilterableAttributeList $filterableAttributeList,
        private readonly FilterList $filterList,
        private readonly StoreManagerInterface $storeManager,
        private readonly Registry $registry,
        private readonly CustomEntityCollectionFactory $entityCollectionFactory,
        private readonly RequestInterface $request,
        array $data = []
    ) {
        $this->collectionProvider = $context->getCollectionProvider();
        $this->stateKeyGenerator = $context->getStateKey();
        $this->collectionFilter = $context->getCollectionFilter();

        parent::__construct($data);
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if (!$this->_stateKey) {
            $this->_stateKey = $this->stateKeyGenerator->toString($this->getCurrentAttributeSet());
        }
        return $this->_stateKey;
    }

    /**
     * Retrieve current layer entity collection
     *
     * @return CustomEntityCollection
     */
    public function getEntityCollection(): CustomEntityCollection
    {
        if (isset($this->_entityCollections[$this->getCurrentAttributeSet()->getAttributeSetId()])) {
            $collection = $this->_entityCollections[$this->getCurrentAttributeSet()->getAttributeSetId()];
        } else {
            $collection = $this->collectionProvider->getCollection($this->getCurrentAttributeSet());
            $this->prepareEntityCollection($collection);
            $this->_entityCollections[$this->getCurrentAttributeSet()->getAttributeSetId()] = $collection;
        }

        return $collection;
    }

    /**
     * Prepare collection with filtering and sorting
     *
     * @param CustomEntityCollection $collection
     * @return Layer
     */
    public function prepareEntityCollection(CustomEntityCollection $collection): Layer
    {
        $this->collectionFilter->filter($collection, $this->getCurrentAttributeSet());

        return $this;
    }

    /**
     * Apply layer
     * Method is colling after apply all filters, can be used
     * for prepare some index data before getting information
     * about existing indexes
     *
     * @return \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer
     */
    public function apply()
    {
        $stateSuffix = '';
        foreach ($this->getState()->getFilters() as $filterItem) {
            $stateSuffix .= '_' . $filterItem->getFilter()->getRequestVar() . '_' . $filterItem->getValueString();
        }
        if (!empty($stateSuffix)) {
            $this->_stateKey = $this->getStateKey() . $stateSuffix;
        }

        return $this;
    }

    /**
     * Retrieve current store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Get current attribute set from registry
     *
     * @return AttributeSet|null
     */
    public function getCurrentAttributeSet(): ?AttributeSet
    {
        $set = $this->getData(self::REGISTRY_KEY_ATTRIBUTE_SET);
        if ($set === null) {
            $set = $this->registry->registry(self::REGISTRY_KEY_ATTRIBUTE_SET);
            if ($set) {
                $this->setData(self::REGISTRY_KEY_ATTRIBUTE_SET, $set);
            }
        }

        return $set;
    }

    /**
     * Get filterable attributes for current attribute set
     *
     * @return \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface[]
     */
    public function getFilterableAttributes(): array
    {
        $attributeSet = $this->getCurrentAttributeSet();
        if (!$attributeSet) {
            return [];
        }

        return $this->filterableAttributeList->getList($this);
    }

    /**
     * Check if layer has any filterable attributes
     *
     * @return bool
     */
    public function hasFilterableAttributes(): bool
    {
        return !empty($this->getFilterableAttributes());
    }

    /**
     * Retrieve layer state object
     *
     * @return State
     */
    public function getState()
    {
        $state = $this->getData('state');
        if ($state === null) {
            \Magento\Framework\Profiler::start(__METHOD__);
            $state = $this->_layerStateFactory->create();
            $this->setData('state', $state);
            \Magento\Framework\Profiler::stop(__METHOD__);
        }

        return $state;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_entityCollections = [];
    }
}