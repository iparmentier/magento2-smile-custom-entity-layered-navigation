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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Plugin\Controller\Set;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page as ResultPage;
use Smile\CustomEntity\Controller\Set\View as SetViewController;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Resolver;

class ViewPlugin
{
    /**
     * @var Resolver
     */
    private Resolver $layerResolver;

    /**
     * @param Resolver $layerResolver
     */
    public function __construct(
        Resolver $layerResolver
    ) {
        $this->layerResolver = $layerResolver;
    }

    /**
     * Add layered navigation layout handle after action execution if conditions are met
     *
     * @param SetViewController $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterExecute(SetViewController $subject, ResultInterface $result): ResultInterface
    {
        if ($result instanceof ResultPage) {
            $layer = $this->layerResolver->get();

            if ($layer->hasFilterableAttributes()) {
                $result->addPageLayoutHandles(['type' => 'layered']);
            }
        }
        return $result;
    }
}