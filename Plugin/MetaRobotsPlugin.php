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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Plugin;

use Smile\CustomEntity\Controller\Set\View;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\Page;

/**
 * Plugin to set NOINDEX,FOLLOW meta robots on filtered pages
 */
class MetaRobotsPlugin
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var PageConfig
     */
    private PageConfig $pageConfig;

    /**
     * @param RequestInterface $request
     * @param PageConfig $pageConfig
     */
    public function __construct(
        RequestInterface $request,
        PageConfig $pageConfig
    ) {
        $this->request = $request;
        $this->pageConfig = $pageConfig;
    }

    /**
     * Execute
     *
     * @param View $subject
     * @param Page $page
     *
     * @return mixed
     */
    public function afterExecute(View $subject, $resultPage)
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
     * Check if current page is a custom entity page
     *
     * @return bool
     */
    private function isCustomEntityPage(): bool
    {
        $moduleName = $this->request->getModuleName();
        $controllerName = $this->request->getControllerName();
        return $moduleName === 'custom_entity' && $controllerName === 'set';
    }
    
    /**
     * Check if filters are applied to the current page
     *
     * @param ResultInterface $resultPage
     * @return bool
     */
    private function hasAppliedFilters($resultPage): bool
    {
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
