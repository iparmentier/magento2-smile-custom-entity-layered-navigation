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

use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Element\Template;
use Smile\CustomEntity\Api\CustomEntityRepositoryInterface;
use Smile\CustomEntity\Api\Data\CustomEntityInterface;
use Smile\CustomEntity\Block\Html\Pager;

/**
 * Attribute set view block.
 * Inherits from Smile_CustomEntity view block and adds specific logic.
 * Will potentially interact with the layered navigation components later.
 */
class View extends SmileCustomEntityView
{
    /**
     * View constructor.
     *
     * @param Template\Context $context Context.
     * @param Registry $registry Registry.
     * @param CustomEntityRepositoryInterface $customEntityRepository Custom entity repository.
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory Search criteria builder factory.
     * @param array $data Block data.
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        CustomEntityRepositoryInterface $customEntityRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $customEntityRepository, $searchCriteriaBuilderFactory, $data);
    }

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
        return $this->getChildHtml('set_additional');
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

        if (count($found) > 0) {
            return true;
        }
        return false;
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