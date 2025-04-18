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

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Resolver as LayerResolver;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\AvailabilityFlagInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterList;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\AbstractFilter;

/**
 * Layered Navigation View Block for Custom Entities.
 * Main container for filters.
 */
class Navigation extends Template
{
    /**
     * Entity listing toolbar block name
     */
    private const ENTITY_LISTING_TOOLBAR_BLOCK = 'set_list_toolbar';

    /**
     * @var Layer
     */
    private Layer $_entityLayer;

    /**
     * @var FilterList
     */
    protected FilterList $filterList;

    /**
     * @var AvailabilityFlagInterface
     */
    protected AvailabilityFlagInterface $visibilityFlag;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param FilterList $filterList
     * @param AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        FilterList $filterList,
        AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        $this->_entityLayer = $layerResolver->get();
        $this->filterList = $filterList;
        $this->visibilityFlag = $visibilityFlag;
        parent::__construct($context, $data);
    }

    /**
     * Apply layer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->filterList->getFilters($this->_entityLayer) as $filter) {
            $filter->apply($this->getRequest());
        }

        $this->getLayer()->apply();

        return parent::_prepareLayout();
    }

    /**
     * @inheritdoc
     * @since 100.3.4
     */
    protected function _beforeToHtml()
    {
        $this->configureToolbarBlock();

        return parent::_beforeToHtml();
    }

    /**
     * Get layer object
     *
     * @return Layer
     */
    public function getLayer(): Layer
    {
        return $this->_entityLayer;
    }

    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filterList->getFilters($this->_entityLayer);
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock(): bool
    {
        return $this->getLayer()->getCurrentAttributeSet()
            && $this->visibilityFlag->isEnabled($this->getLayer(), $this->getFilters());
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getChildBlock('state')->getClearUrl();
    }

    /**
     * Configures the Toolbar block
     *
     * @return void
     */
    private function configureToolbarBlock(): void
    {
        /** @var Toolbar $toolbarBlock */
        $toolbarBlock = $this->getLayout()->getBlock(self::ENTITY_LISTING_TOOLBAR_BLOCK);
        if ($toolbarBlock) {
            /** @var Collection $collection */
            $collection = $this->getLayer()->getEntityCollection();
            $toolbarBlock->setCollection($collection);
        }
    }
}