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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\State;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;

/**
 * Layer Resource Model.
 * Provides methods to interact with the entity index based on layer state.
 */
class Layer
{
    private const INDEX_TABLE_ALIAS = 'idx';
    private const AGGREGATION_FIELD = 'value'; // Column storing option IDs or boolean values

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var State
     */
    private State $state;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param State $state
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        State $state
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * Get a SELECT object for entity IDs matching the currently applied filters,
     * optionally excluding one specific filter (used for calculating facet counts).
     *
     * @param int $storeId
     * @param int $attributeSetId
     * @param int|null $excludeAttributeId Attribute ID to exclude from filtering (for facet counts).
     * @return Select|null
     * @throws LocalizedException
     */
    public function getBaseSelectForFacets(int $storeId, int $attributeSetId, ?int $excludeAttributeId = null): ?Select
    {
        $connection = $this->resourceConnection->getConnection();
        $indexTable = $this->resourceConnection->getTableName('amadeco_custom_entity_index_eav_idx');
        $entityTable = $this->resourceConnection->getTableName('smile_custom_entity');

        if (!$connection->isTableExists($indexTable)) {
            return null;
        }

        $appliedFilters = $this->state->getFiltersData(); // ['attribute_id' => value(s), ...]

        if ($excludeAttributeId !== null && isset($appliedFilters[$excludeAttributeId])) {
            unset($appliedFilters[$excludeAttributeId]);
        }

        $select = $connection->select();
        $select->from(['e' => $entityTable], ['entity_id'])
            ->where('e.attribute_set_id = ?', $attributeSetId);

        $select->joinLeft(
            ['ea' => $this->resourceConnection->getTableName('smile_custom_entity_int')],
            "e.entity_id = ea.entity_id AND ea.attribute_id = (
                SELECT attribute_id FROM eav_attribute
                WHERE attribute_code = 'is_active' AND entity_type_id = (
                    SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'smile_custom_entity'
                )
            ) AND ea.store_id IN (0, {$storeId})",
            []
        )
        ->where('ea.value = 1');

        if (empty($appliedFilters)) {
            return $select;
        }

        $aliasCounter = 0;
        foreach ($appliedFilters as $attributeId => $value) {
            $alias = 'filter_' . $aliasCounter++;

            if (is_array($value)) {
                $joinConditions = [];
                foreach ($value as $singleValue) {
                    $joinConditions[] = $connection->quoteInto(
                        "{$alias}.value = ?",
                        $singleValue
                    );
                }

                $joinCondition = sprintf(
                    "%s.entity_id = e.entity_id AND %s.attribute_id = %d AND %s.store_id = %d AND (%s)",
                    $alias,
                    $alias,
                    (int)$attributeId,
                    $alias,
                    $storeId,
                    implode(' OR ', $joinConditions)
                );
            } else {
                $joinCondition = sprintf(
                    "%s.entity_id = e.entity_id AND %s.attribute_id = %d AND %s.store_id = %d AND %s",
                    $alias,
                    $alias,
                    (int)$attributeId,
                    $alias,
                    $storeId,
                    $connection->quoteInto("{$alias}.value = ?", $value)
                );
            }

            $select->joinInner(
                [$alias => $indexTable],
                $joinCondition,
                []
            );
        }

        return $select;
    }
}