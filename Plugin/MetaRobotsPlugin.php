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
 * @copyright Copyright (c) Amadeco[](https://www.amadeco.fr) - Ilan Parmentier
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\Page;
use Smile\CustomEntity\Controller\Set\View;

/**
 * Plugin to set NOINDEX,FOLLOW meta robots on filtered custom entity pages.
 */
class MetaRobotsPlugin
{
    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;

    /**
     * @var PageConfig
     */
    private readonly PageConfig $pageConfig;

    /**
     * Constructor.
     *
     * @param RequestInterface $request    The HTTP request object.
     * @param PageConfig       $pageConfig The page configuration object.
     */
    public function __construct(
        RequestInterface $request,
        PageConfig $pageConfig
    ) {
        $this->request = $request;
        $this->pageConfig = $pageConfig;
    }

    /**
     * Sets meta robots to NOINDEX,FOLLOW if filters are applied on custom entity pages.
     *
     * @param View            $subject    The original controller action.
     * @param ResultInterface $resultPage The result from the controller execution.
     *
     * @return ResultInterface The modified or original result page.
     */
    public function afterExecute(View $subject, ResultInterface $resultPage): ResultInterface
    {
        if (!$this->isCustomEntityPage()) {
            return $resultPage;
        }

        if ($this->hasAppliedFilters($resultPage)) {
            $this->pageConfig->setMetadata('robots', 'NOINDEX,FOLLOW');
        }

        return $resultPage;
    }

    /**
     * Checks if the current page is a custom entity page.
     *
     * @return bool True if it's a custom entity page, false otherwise.
     */
    private function isCustomEntityPage(): bool
    {
        $moduleName = $this->request->getModuleName();
        $controllerName = $this->request->getControllerName();

        return $moduleName === 'custom_entity' && $controllerName === 'set';
    }

    /**
     * Checks if filters are applied to the current page.
     *
     * @param ResultInterface $resultPage The controller result object.
     *
     * @return bool True if filters are applied, false otherwise.
     */
    private function hasAppliedFilters(ResultInterface $resultPage): bool
    {
        if (!$resultPage instanceof Page) {
            return false;
        }

        $layout = $resultPage->getLayout();
        if (!$layout) {
            return false;
        }

        $state = $layout->getBlock('set.layer.state');
        if ($state && $state->getActiveFilters()) {
            return true;
        }

        return false;
    }
}
