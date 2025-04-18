<?php

declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\CustomEntity\Attribute;

use Smile\CustomEntity\Model\ResourceModel\CustomEntity\Attribute\Collection as SmileAttributeCollection;

/**
 * Extended Custom Entity Attribute Collection
 * Adds filtering capabilities for layered navigation
 */
class Collection extends SmileAttributeCollection
{
    /**
     * Allowed frontend input types for filtering
     * @var string[]
     */
    private const FILTERABLE_INPUT_TYPES = ['select', 'multiselect', 'boolean'];

    /**
     * Add filterable filter to collection
     *
     * @param mixed $condition Filter condition. Can be int or array
     * @return $this
     */
    public function addIsFilterableFilter(): self
    {
        $this->addFieldToFilter('main_table.frontend_input', ['in' => self::FILTERABLE_INPUT_TYPES]);
        $this->addFieldToFilter('additional_table.is_filterable', ['gt' => 0]);
        return $this;
    }

    /**
     * Add field to filter by
     *
     * @param string|array $field Field(s) to filter
     * @param mixed $condition Condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null): self
    {
        if ($field === 'is_filterable') {
            return $this->addIsFilterableFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }
}