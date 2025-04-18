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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;

use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Layer Resolver
 *
 * @api
 */
class Resolver implements ResetAfterRequestInterface
{
    /**
     * Filter factory
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Layer
     */
    protected $layer = null;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Catalog Layer by specified type
     *
     * @param string $layerType
     * @return void
     */
    public function create($layerType)
    {
        if (isset($this->layer)) {
            throw new \RuntimeException('Custom Entity Set Layer has been already created');
        }
        $this->layer = $this->objectManager->create(Layer::class);
    }

    /**
     * Get current Catalog Layer
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function get()
    {
        if (!isset($this->layer)) {
            $this->layer = $this->objectManager->create(Layer::class);
        }
        return $this->layer;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->layer = null;
    }
}
