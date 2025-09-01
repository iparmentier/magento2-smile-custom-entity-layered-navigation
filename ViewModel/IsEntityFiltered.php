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

namespace Amadeco\SmileCustomEntityLayeredNavigation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Amadeco\SmileCustomEntityLayeredNavigation\Block\SetList as EntitiesPager;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer as EntitiesLayer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\SetList\Toolbar as ToolbarModel;
use Magento\Theme\Block\Html\Pager as ProductsPager;

class IsEntityFiltered implements ArgumentInterface
{
    private Layer $catalogLayer;

    /**
     * @param RequestInterface $request
     * @param Resolver $layerResolver
     * @param ProductsPager $productsPager
     * @param EntitiesPager $entitiesPager
     * @param EntitiesLayer $entitiesLayer
     */
    public function __construct(
        private RequestInterface $request,
        private Resolver $layerResolver,
        private ProductsPager $productsPager,
        private EntitiesPager $entitiesPager,
        private EntitiesLayer $entitiesLayer
    ) {
        $this->request = $request;
        $this->catalogLayer = $layerResolver->get();
        $this->productsPager = $productsPager;
        $this->entitiesPager = $entitiesPager;
        $this->entitiesLayer = $entitiesLayer;
    }

    /**
     * Check whether the current entity is filtered
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        $params = [
            ToolbarModel::PAGE_PARAM_NAME,
            ToolbarModel::ORDER_PARAM_NAME,
            ToolbarModel::DIRECTION_PARAM_NAME,
            ToolbarModel::MODE_PARAM_NAME,
            ToolbarModel::LIMIT_PARAM_NAME
        ];

        $requestParams = array_keys($this->request->getParams());
        $found = array_intersect($params, $requestParams);

        return (
            count($found) > 0 ||
            count($this->catalogLayer->getState()->getFilters()) > 0 ||
            count($this->entitiesLayer->getState()->getFilters()) > 0
        );
    }
}
