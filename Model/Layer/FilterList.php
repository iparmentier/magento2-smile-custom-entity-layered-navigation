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

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Smile\CustomEntity\Model\CustomEntity\Attribute;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\AbstractFilter;

/**
 * Filter List Model for Custom Entity Layered Navigation
 *
 * Manages the available attribute filters for the current layer
 */
class FilterList implements ResetAfterRequestInterface
{
    public const ATTRIBUTE_FILTER = 'attribute';
    public const BOOLEAN_FILTER = 'boolean';

    /**
     * @var AbstractFilter[]
     */
    private array $filters = [];

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var FilterableAttributeList
     */
    private FilterableAttributeList $filterableAttributes;

    /**
     * @var string[]
     */
    protected $filterTypes = [
        self::ATTRIBUTE_FILTER => \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\Attribute::class,
        self::BOOLEAN_FILTER => \Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\Filter\Boolean::class
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param FilterableAttributeList $filterableAttributes
     * @param LoggerInterface $logger
     * @param array $filters Filter types configuration
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeList $filterableAttributes,
        array $filters = []
    ) {
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;

        /** Override default filter type models */
        $this->filterTypes = array_merge($this->filterTypes, $filters);
    }

    /**
     * Retrieve list of filters
     *
     * @param Layer $layer
     * @return array|Filter\AbstractFilter[]
     */
    public function getFilters(Layer $layer): array
    {
        if (!count($this->filters)) {
            foreach ($this->filterableAttributes->getList() as $attribute) {
                $this->filters[] = $this->createAttributeFilter($attribute, $layer);
            }
        }
        return $this->filters;
    }

    /**
     * Create filter
     *
     * @param Attribute $attribute
     * @param Layer $layer
     * @return AbstractFilter
     */
    protected function createAttributeFilter(Attribute $attribute, Layer $layer): AbstractFilter
    {
        $filterClassName = $this->getAttributeFilterClass($attribute);

        $filter = $this->objectManager->create(
            $filterClassName,
            ['data' => ['attribute_model' => $attribute], 'layer' => $layer]
        );
        return $filter;
    }

    /**
     * Get Attribute Filter Class Name
     *
     * @param Attribute $attribute
     * @return string
     */
    protected function getAttributeFilterClass(Attribute $attribute): string
    {
        $filterClassName = $this->filterTypes[self::ATTRIBUTE_FILTER];

        if ($attribute->getFrontendInput() === 'boolean') {
            $filterClassName = $this->filterTypes[self::BOOLEAN_FILTER];
        }

        return $filterClassName;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->filters = [];
    }
}