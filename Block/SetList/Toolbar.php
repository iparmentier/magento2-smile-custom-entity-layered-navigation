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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Block\SetList;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\View\Element\Template;
use Smile\CustomEntity\Api\CustomEntityRepositoryInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Helper\SetList;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Config\Source\SortBy;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\SetList\Toolbar as ToolbarModel;

/**
 * Product list toolbar
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Toolbar extends Template
{
    /**
     * Set collection
     *
     * @var \Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection
     */
    protected $_collection = null;

    /**
     * List of available limits
     *
     * @var array
     */
    protected $_availableLimit = null;

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder = null;

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    protected $_enableViewSwitcher = true;

    /**
     * @var bool
     */
    protected $_isExpanded = true;

    /**
     * Default Order field
     *
     * @var string
     */
    protected $_orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction = SetList::DEFAULT_SORT_DIRECTION;

    /**
     * @var string
     */
    protected $_template = 'Smile_CustomEntity::set/list/toolbar.phtml';

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @param Template\Context $context
     * @param ToolbarModel $toolbarModel
     * @param EncoderInterface $urlEncoder
     * @param SetList $setListHelper
     * @param PostHelper $postDataHelper
     * @param CustomEntityRepositoryInterface $customEntityRepository
     * @param array $data
     * @param FormKey|null $formKey
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Template\Context $context,
        protected ToolbarModel $toolbarModel,
        protected EncoderInterface $urlEncoder,
        protected SetList $setListHelper,
        protected PostHelper $postDataHelper,
        private CustomEntityRepositoryInterface $customEntityRepository,
        array $data = [],
        ?\Magento\Framework\Data\Form\FormKey $formKey = null
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_setListHelper = $setListHelper;
        $this->postDataHelper = $postDataHelper;

        $this->formKey = $formKey ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Data\Form\FormKey::class
        );
        parent::__construct($context, $data);
    }

    /**
     * Set collection to pager
     *
     * @param \Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }

        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }

    /**
     * Return products collection instance
     *
     * @return \Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection
     */
    public function getCollection(): \Smile\CustomEntity\Model\ResourceModel\CustomEntity\Collection|null
    {
        return $this->_collection;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->toolbarModel->getCurrentPage();
    }

    /**
     * Get grid products sort order field
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->getOrderField();

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarModel->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }

    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $dir = $this->_direction;
        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return $this
     */
    public function setDefaultOrder($field)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }
        return $this;
    }

    /**
     * Set default sort direction
     *
     * @param string $dir
     * @return $this
     */
    public function setDefaultDirection($dir)
    {
        if ($dir && in_array(strtolower($dir), ['asc', 'desc'])) {
            $this->_direction = strtolower($dir);
        }
        return $this;
    }

    /**
     * Retrieve available Order fields list
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        $this->loadAvailableOrders();
        return $this->_availableOrder;
    }

    /**
     * Set Available order fields list
     *
     * @param array $orders
     * @return $this
     */
    public function setAvailableOrders($orders)
    {
        $this->_availableOrder = $orders;
        return $this;
    }

    /**
     * Add order to available orders
     *
     * @param string $order
     * @param string $value
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function addOrderToAvailableOrders($order, $value)
    {
        $this->loadAvailableOrders();
        $this->_availableOrder[$order] = $value;
        return $this;
    }

    /**
     * Remove order from available orders if exists
     *
     * @param string $order
     * @return $this
     */
    public function removeOrderFromAvailableOrders($order)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$order])) {
            unset($this->_availableOrder[$order]);
        }
        return $this;
    }

    /**
     * Compare defined order field with current order field
     *
     * @param string $order
     * @return bool
     */
    public function isOrderCurrent($order)
    {
        return $order === $this->getCurrentOrder();
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [
            '_current' => true,
            '_escape' => false,
            '_use_rewrite' => true,
            '_query' => array_merge($params, ['entity_id' => null])
        ];

        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Get pager encoded url.
     *
     * @param array $params
     * @return string
     */
    public function getPagerEncodedUrl($params = [])
    {
        return $this->urlEncoder->encode($this->getPagerUrl($params));
    }

    /**
     * Disable view switcher
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function disableViewSwitcher()
    {
        $this->_enableViewSwitcher = false;
        return $this;
    }

    /**
     * Enable view switcher
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function enableViewSwitcher()
    {
        $this->_enableViewSwitcher = true;
        return $this;
    }

    /**
     * Is a enabled view switcher
     *
     * @return bool
     */
    public function isEnabledViewSwitcher()
    {
        return $this->_enableViewSwitcher;
    }

    /**
     * Disable Expanded
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function disableExpanded()
    {
        $this->_isExpanded = false;
        return $this;
    }

    /**
     * Enable Expanded
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function enableExpanded()
    {
        $this->_isExpanded = true;
        return $this;
    }

    /**
     * Check is Expanded
     *
     * @return bool
     */
    public function isExpanded()
    {
        return $this->_isExpanded;
    }

    /**
     * Retrieve default per page values
     *
     * @return string (comma separated)
     */
    public function getDefaultPerPageValue()
    {
        if ($default = $this->getDefaultGridPerPage()) {
            return $default;
        }
        return 28;
    }

    /**
     * Set pager limit
     *
     * @param array $limits
     * @return $this
     */
    public function setAvailableLimit(array $limits)
    {
        $this->_availableLimit = $limits;
        return $this;
    }

    /**
     * Retrieve pager limit
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        if ($this->_availableLimit) {
            return $this->_availableLimit;
        }
        return $this->_setListHelper->getAvailableLimit($this->getCurrentMode());
    }

    /**
     * Get specified products limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0] ?? 10;
        }

        $limit = $this->toolbarModel->getLimit();

        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    /**
     * Check if limit is current used in toolbar.
     *
     * @param int $limit
     * @return bool
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    /**
     * Pager number of items from which products started on current page.
     *
     * @return int
     */
    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * Pager number of items products finished on current page.
     *
     * @return int
     */
    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * Total number of products in current category.
     *
     * @return int
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * Check if current page is the first.
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    /**
     * Return last page number.
     *
     * @return int
     */
    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('set_list_toolbar_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /** @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimitVarName(
                ToolbarModel::LIMIT_PARAM_NAME
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );

            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * Retrieve widget options in json format
     *
     * @param array $customOptions Optional parameter for passing custom selectors from template
     * @return string
     */
    public function getWidgetOptionsJson(array $customOptions = [])
    {
        $options = [
            'mode' => ToolbarModel::MODE_PARAM_NAME,
            'direction' => ToolbarModel::DIRECTION_PARAM_NAME,
            'order' => ToolbarModel::ORDER_PARAM_NAME,
            'limit' => ToolbarModel::LIMIT_PARAM_NAME,
            'modeDefault' => 'grid',
            'directionDefault' => $this->_direction,
            'orderDefault' => $this->getOrderField(),
            'limitDefault' => $this->getDefaultPerPageValue(),
            'url' => $this->getPagerUrl(),
            'formKey' => $this->formKey->getFormKey()
        ];
        $options = array_replace_recursive($options, $customOptions);
        return json_encode(['productListToolbarForm' => $options]);
    }

    /**
     * Get order field
     *
     * @return null|string
     */
    protected function getOrderField()
    {
        if ($this->_orderField === null) {
            $this->_orderField = $this->_setListHelper->getDefaultSortField();
        }
        return $this->_orderField;
    }

    /**
     * Load Available Orders
     *
     * @return $this
     */
    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = SortBy::toArray();
        }
        return $this;
    }
}