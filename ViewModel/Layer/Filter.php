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

namespace Amadeco\SmileCustomEntityLayeredNavigation\ViewModel\Layer;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Helper\SetList;

/**
 * View model for layered navigation filters
 */
class Filter implements ArgumentInterface
{
    /**
     * @var SetList
     */
    private SetList $setListHelper;

    /**
     * @param SetList $setListHelper
     */
    public function __construct(
        SetList $setListHelper
    ) {
        $this->setListHelper = $setListHelper;
    }

    /**
     * Check if should display set count in layered navigation
     *
     * @return bool
     */
    public function shouldDisplayEntityCountOnLayer(): bool
    {
        return true;
    }
}