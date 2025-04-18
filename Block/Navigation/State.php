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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Block\Navigation;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Resolver as LayerResolver;

/**
 * Layered Navigation State Block
 */
class State extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amadeco_SmileCustomEntityLayeredNavigation::layer/state.phtml';

    /**
     * @var LayerResolver
     */
    private LayerResolver $layerResolver;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        array $data = []
    ) {
        $this->_entityLayer = $layerResolver->get();
        parent::__construct($context, $data);
    }

    /**
     * Get Active Filters
     *
     * @return \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\Item[]
     */
    public function getActiveFilters(): array
    {
        return $this->getLayer()->getState()->getFilters();
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        $filterState = [];
        foreach ($this->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return $this->_urlBuilder->getUrl('*/*/*', $params);
    }

    /**
     * Retrieve Layer object
     *
     * @return \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer
     */
    public function getLayer()
    {
        if (!$this->hasData('layer')) {
            $this->setLayer($this->_entityLayer);
        }
        return $this->_getData('layer');
    }
}