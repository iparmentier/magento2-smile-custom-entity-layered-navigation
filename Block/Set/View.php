<?php
/**
 * Amadeco SmileCustomEntityLayeredNavigation module
 *
 * @category  Amadeco
 * @package   Amadeco_SmileCustomEntityLayeredNavigation
 * @copyright Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Block\Set;

use Smile\CustomEntity\Model\CustomEntity;
use Smile\CustomEntity\Block\Set\View as SmileCustomEntityView;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\SetList\Toolbar as ToolbarModel;

/**
 * Attribute set view block.
 * Inherits from Smile_CustomEntity view block and adds specific logic.
 * Will potentially interact with the layered navigation components later.
 */
class View extends SmileCustomEntityView
{
    /**
     * Return entity list html
     *
     * @return string
     */
    public function getEntityListHtml()
    {
        return $this->getChildHtml('set_list');
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
     * Check if the entity list is filtered
     *
     * @return bool
     */
    public function isFilteredEntity(): bool
    {
        $params = [
            ToolbarModel::PAGE_PARAM_NAME,
            ToolbarModel::ORDER_PARAM_NAME,
            ToolbarModel::DIRECTION_PARAM_NAME,
            ToolbarModel::MODE_PARAM_NAME,
            ToolbarModel::LIMIT_PARAM_NAME
        ];

        $requestParams = array_keys($this->_request->getParams());
        $found = array_intersect($params, $requestParams);

        return count($found) > 0;
    }

    /**
     * Return block identities for cache
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $identities = [];

        if ($attributeSet = $this->getAttributeSet()) {
            $identities[] = CustomEntity::CACHE_CUSTOM_ENTITY_SET_TAG . '_' . $attributeSet->getAttributeSetId();
        }

        return $identities;
    }
}