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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Smile\CustomEntity\Model\CustomEntity;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\CollectionFactory as CustomEntityCollectionFactory;
use Amadeco\SmileCustomEntityLayeredNavigation\Block\SetList\Toolbar;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Resolver as LayerResolver;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\Attribute\Source\SortBy;

/**
 * Custom Entity Set List Block
 *
 * Block responsible for rendering the list of custom entities for an attribute set
 * with layered navigation applied
 */
class SetList extends Template implements IdentityInterface
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    /**
     * Collection for the current attribute set
     *
     * @var Collection
     */
    protected $_entityCollection;

    /**
     * @param Template\Context $context
     * @param PostHelper $postDataHelper
     * @param CustomEntityCollectionFactory $customEntityCollectionFactory
     * @param LayerResolver $layerResolver
     * @param array $data Block data.
     */
    public function __construct(
        protected Context $context,
        protected PostHelper $postDataHelper,
        private CustomEntityCollectionFactory $customEntityCollectionFactory,
        private LayerResolver $layerResolver,
        array $data = []
    ) {
        $this->_entityLayer = $layerResolver->get();

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Retrieve loaded entity collection
     *
     * The goal of this method is to choose whether the existing collection should be returned
     * or a new one should be initialized.
     *
     * It is not just a caching logic, but also is a real logical check
     * because there are two ways how collection may be stored inside the block:
     *   - Entity collection may be passed externally by 'setCollection' method
     *   - Entity collection may be requested internally from the current custom entity set Layer.
     *
     * And this method will return collection anyway,
     * even when it did not pass externally and therefore isn't cached yet
     *
     * @return \Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection
     */
    protected function _getEntityCollection(): Collection
    {
        if (null === $this->_entityCollection) {
            $this->_entityCollection = $this->getLayer()->getEntityCollection();
        }

        return $this->_entityCollection;
    }

    /**
     * Retrieve entity layer model
     *
     * @return \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer
     */
    public function getLayer()
    {
        return $this->_entityLayer;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    public function getLoadedEntityCollection(): Collection
    {
        return $this->_getEntityCollection();
    }

    /**
     * Return current attribute set.
     */
    public function getAttributeSet(): ?AttributeSetInterface
    {
        $layer = $this->getLayer();
        return $layer->getCurrentAttributeSet();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        if ($this->getChildBlock('toolbar')) {
            return $this->getChildBlock('toolbar')->getCurrentMode();
        }

        return $this->getDefaultListingMode();
    }

    /**
     * Get listing mode for entities if toolbar is removed from layout.
     * Use the general configuration for entity list mode from config path catalog/custom_entity/list_mode as default value
     * or mode data from block declaration from layout.
     *
     * @return string
     */
    private function getDefaultListingMode()
    {
        // default Toolbar when the toolbar layout is not used
        $defaultToolbar = $this->getToolbarBlock();
        $availableModes = $defaultToolbar->getModes();

        // layout config mode
        $mode = $this->getData('mode');

        if (!$mode || !isset($availableModes[$mode])) {
            // default config mode
            $mode = $defaultToolbar->getCurrentMode();
        }

        return $mode;
    }

    /**
     * Prepare layout - configure and set up toolbar, apply sorting and filters
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $collection = $this->_getEntityCollection();

        $this->addToolbarBlock($collection);

        if (!$collection->isLoaded()) {
            $collection->load();
        }

        $attributeSetId = $this->getAttributeSet()->getAttributeSetId();
        if ($attributeSetId) {
            foreach ($collection as $entity) {
                $entity->setData('attribute_set_id', $attributeSetId);
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     * Add toolbar block from entity listing layout
     *
     * @param Collection $collection
     */
    private function addToolbarBlock(Collection $collection)
    {
        $toolbarLayout = $this->getToolbarFromLayout();

        if ($toolbarLayout) {
            $this->configureToolbar($toolbarLayout, $collection);
        }
    }

    /**
     * Retrieve Toolbar block from layout or a default Toolbar
     *
     * @return Toolbar
     */
    public function getToolbarBlock()
    {
        $block = $this->getToolbarFromLayout();

        if (!$block) {
            $block = $this->getLayout()->createBlock(
                $this->_defaultToolbarBlock,
                uniqid(microtime())
            );
        }

        return $block;
    }

    /**
     * Get toolbar block from layout
     *
     * @return Toolbar|bool
     */
    private function getToolbarFromLayout()
    {
        $blockName = $this->getToolbarBlockName();

        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block instanceof Toolbar) {
                return $block;
            }
        }

        return false;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Set collection.
     *
     * @param Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_entityCollection = $collection;
        return $this;
    }

    /**
     * Add attribute.
     *
     * @param array|string|integer|Element $code
     * @return $this
     */
    public function addAttribute($code)
    {
        $this->_getEntityCollection()->addAttributeToSelect($code);
        return $this;
    }

    /**
     * Prepare Sort By fields from Custom Entity Set Data
     *
     * @param $set
     * @return $this
     */
    public function prepareSortableFieldsBySet($set)
    {
        $defaultSortBy = SortBy::toArray();
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($defaultSortBy);
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            $entitySortBy = $this->getDefaultSortBy() ?: key($defaultSortBy);
            if ($entitySortBy) {
                if (isset($availableOrders[$entitySortBy])) {
                    $this->setSortBy($entitySortBy);
                }
            }
        }

        return $this;
    }

    /**
     * Return block identities for cache
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $identities = [];

        $entity = $this->getLayer()->getCurrentAttributeSet();
        if ($entity) {
            $identities[] = CustomEntity::CACHE_CUSTOM_ENTITY_SET_TAG . '_' . $entity->getAttributeSetId();
        }

        foreach ($this->_getEntityCollection() as $entity) {
            $identities[] = $entity->getIdentities();
        }

        $identities = array_merge([], ...$identities);

        return $identities;
    }

    /**
     * Configures entity collection from a layer and returns its instance.
     *
     * Also in the scope of a entity collection configuration, this method initiates configuration of Toolbar.
     * The reason to do this is because we have a bunch of legacy code
     * where Toolbar configures several options of a collection and therefore this block depends on the Toolbar.
     *
     * This dependency leads to a situation where Toolbar sometimes called to configure a entity collection,
     * and sometimes not.
     *
     * To unify this behavior and prevent potential bugs this dependency is explicitly called
     * when entity collection initialized.
     *
     * @return Collection
     */
    private function initializeEntityCollection()
    {
        $layer = $this->getLayer();
        $collection = $layer->getEntityCollection();

        $this->prepareSortableFieldsBySet($layer->getCurrentAttributeSet());

        $this->_eventManager->dispatch(
            'amadeco_block_set_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Configures the Toolbar block with options from this block and configured entity collection.
     *
     * The purpose of this method is the one-way sharing of different sorting related data
     * between this block, which is responsible for product list rendering,
     * and the Toolbar block, whose responsibility is a rendering of these options.
     *
     * @param EntityList\Toolbar $toolbar
     * @param Collection $collection
     * @return void
     */
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }
}
