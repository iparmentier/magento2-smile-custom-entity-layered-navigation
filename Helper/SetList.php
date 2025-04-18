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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Helper;

use Magento\Catalog\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns data for toolbars of Sorting and Pagination
 *
 * @api
 * @since 100.0.2
 */
class SetList
{
    /**
     * Configuration paths
     */
    public const XML_PATH_CUSTOM_ENTITY_LIST_MODE = 'custom_entity/storefront/list_mode';
    public const XML_PATH_CUSTOM_ENTITY_SORT_FIELD = 'custom_entity/storefront/default_sort_by';
    public const XML_PATH_DISPLAY_ENTITY_COUNT = 'custom_entity/layered_navigation/display_entity_count';

    /**
     * @var string Default sort direction for the entity list
     */
    public const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * @var string
     */
    public const VIEW_MODE_LIST = 'list';
    public const VIEW_MODE_GRID = 'grid';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit = [20 => 20, 36 => 36, 52 => 52];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $coreRegistry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ?Registry $coreRegistry = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry ?? ObjectManager::getInstance()->get(Registry::class);
    }

    /**
     * Returns available mode for view
     *
     * @return array|null
     */
    public function getAvailableViewMode()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_ENTITY_LIST_MODE, ScopeInterface::SCOPE_STORE);

        switch ($value) {
            case 'grid':
                return ['grid' => __('Grid')];

            case 'list':
                return ['list' => __('List')];

            case 'grid-list':
                return ['grid' => __('Grid'), 'list' => __('List')];

            case 'list-grid':
                return ['list' => __('List'), 'grid' => __('Grid')];
        }

        return null;
    }

    /**
     * Returns default view mode
     *
     * @param array $options
     * @return string
     */
    public function getDefaultViewMode($options = [])
    {
        if (empty($options)) {
            $options = $this->getAvailableViewMode();
        }

        return current(array_keys($options));
    }

    /**
     * Get default sort field
     *
     * @return null|string
     */
    public function getDefaultSortField()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_ENTITY_SORT_FIELD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $viewMode
     * @return array
     */
    public function getAvailableLimit($viewMode): array
    {
        $availableViewModes = $this->getAvailableViewMode();

        if (!isset($availableViewModes[$viewMode])) {
            return $this->_defaultAvailableLimit;
        }

        $perPageConfigPath = 'custom_entity/storefront/' . $viewMode . '_per_page_values';
        $perPageValues = (string)$this->scopeConfig->getValue($perPageConfigPath, ScopeInterface::SCOPE_STORE);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag('custom_entity/storefront/list_allow_all', ScopeInterface::SCOPE_STORE)) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }

    /**
     * Returns default value of `per_page` for view mode provided
     *
     * @param string $viewMode
     * @return int
     */
    public function getDefaultLimitPerPageValue($viewMode): int
    {
        $xmlConfigPath = sprintf('custom_entity/storefront/%s_per_page', $viewMode);
        $defaultLimit = $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE);

        $availableLimits = $this->getAvailableLimit($viewMode);
        return (int)($availableLimits[$defaultLimit] ?? current($availableLimits));
    }

    /**
     * Check if product count should be displayed in layered navigation
     *
     * @return bool
     */
    public function shouldDisplayEntityCountOnLayer(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DISPLAY_ENTITY_COUNT, ScopeInterface::SCOPE_STORE);
    }
}