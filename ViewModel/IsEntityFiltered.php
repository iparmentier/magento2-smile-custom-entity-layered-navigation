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
use Magento\Catalog\Model\Layer as CatalogLayer;
use Magento\Catalog\Model\Layer\Resolver as CatalogLayerResolver;
use Amadeco\SmileCustomEntityLayeredNavigation\Block\SetList as EntitiesPager;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer as EntitiesLayer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Resolver as EntitiesLayerResolver;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterList as EntitiesFilterList;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\SetList\Toolbar as ToolbarModel;
use Magento\Theme\Block\Html\Pager as ProductsPager;

class IsEntityFiltered implements ArgumentInterface
{
    private CatalogLayer $catalogLayer;
    private EntitiesLayer $entitiesLayer;

    /**
     * @param RequestInterface      $request
     * @param CatalogLayerResolver  $layerResolver
     * @param ProductsPager         $productsPager
     * @param EntitiesPager         $entitiesPager
     * @param EntitiesLayerResolver $entitiesLayerResolver
     * @param EntitiesFilterList    $filterList
     */
    public function __construct(
        private RequestInterface $request,
        private CatalogLayerResolver $layerResolver,
        private ProductsPager $productsPager,
        private EntitiesPager $entitiesPager,
        private EntitiesLayerResolver $entitiesLayerResolver,
        private EntitiesFilterList $filterList
    ) {
        $this->request       = $request;
        $this->catalogLayer  = $layerResolver->get();
        $this->productsPager = $productsPager;
        $this->entitiesPager = $entitiesPager;
        $this->entitiesLayer = $entitiesLayerResolver->get();
        $this->filterList    = $filterList;
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

        $entityFiltersApplied = false;
        foreach ($this->filterList->getFilters($this->entitiesLayer) as $filter) {
            if ($this->request->getParam($filter->getRequestVar()) !== null) {
                $entityFiltersApplied = true;
                break;
            }
        }

        return (
            count($found) > 0 ||
            count($this->catalogLayer->getState()->getFilters()) > 0 ||
            $entityFiltersApplied
        );
    }
}
