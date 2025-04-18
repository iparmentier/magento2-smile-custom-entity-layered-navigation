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
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\AbstractFilter;
use Amadeco\SmileCustomEntityLayeredNavigation\Block\Navigation\FilterRendererInterface;

/**
 * Set Layer filter renderer
 */
class FilterRenderer extends Template implements FilterRendererInterface
{
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(AbstractFilter $filter)
    {
        $this->assign('filterItems', $filter->getItems());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }
}