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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\Layer\Filter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;

/**
 * Resource model for attribute filter operations.
 * Provides counts for attribute options considering applied filters.
 */
class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amadeco_smile_custom_entity_set_idx', 'entity_id');
    }

    /**
     * Apply attribute filter to entity collection
     *
     * @param $filter
     * @param int $value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getEntityCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->getConnection();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value = ?", $value),
        ];

        $collection->getSelect()->join(
            [$tableAlias => $this->getMainTable()],
            implode(' AND ', $conditions),
            []
        );

        return $this;
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function getCount(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        // clone select from collection with filters
        $select = clone $filter->getLayer()->getEntityCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $connection = $this->getConnection();
        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        ];

        $select->join(
            [$tableAlias => $this->getMainTable()],
            join(' AND ', $conditions),
            ['value', 'count' => new \Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")]
        )->group(
            "{$tableAlias}.value"
        );

        return $connection->fetchPairs($select);
    }
}