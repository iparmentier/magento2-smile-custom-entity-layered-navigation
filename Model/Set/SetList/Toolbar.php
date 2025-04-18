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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Set\SetList;

/**
 * Class Toolbar
 *
 * @api
 * @since 100.0.2
 */
class Toolbar
{
    /**
     * GET parameter page variable name
     */
    public const PAGE_PARAM_NAME = 'p';

    /**
     * Sort order cookie name
     */
    public const ORDER_PARAM_NAME = 'product_list_order';

    /**
     * Sort direction cookie name
     */
    public const DIRECTION_PARAM_NAME = 'product_list_dir';

    /**
     * Sort mode cookie name
     */
    public const MODE_PARAM_NAME = 'product_list_mode';

    /**
     * Products per page limit order cookie name
     */
    public const LIMIT_PARAM_NAME = 'product_list_limit';

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        return $this->request->getParam(self::ORDER_PARAM_NAME);
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        return $this->request->getParam(self::DIRECTION_PARAM_NAME);
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        return $this->request->getParam(self::MODE_PARAM_NAME);
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        return $this->request->getParam(self::LIMIT_PARAM_NAME);
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        $page = (int) $this->request->getParam(self::PAGE_PARAM_NAME);
        return $page ?: 1;
    }
}