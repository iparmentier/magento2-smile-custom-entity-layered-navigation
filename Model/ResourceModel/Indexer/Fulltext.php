<?php
/**
 * Copyright © Amadeco. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Smile\CustomEntity\Api\Data\CustomEntityInterface;
use Smile\CustomEntity\Model\ResourceModel\CustomEntity\CollectionFactory as EntityCollectionFactory;
use Amadeco\SmileCustomEntityLayeredNavigation\Api\Data\FilterableAttributeInterface;
use Amadeco\SmileCustomEntityLayeredNavigation\Model\Layer\FilterableAttributeList;

/**
 * Resource Model for Custom Entity Layered Navigation Indexer.
 *
 * Handles the indexing of filterable attributes for custom entities to enable
 * layered navigation functionality.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Fulltext
{
    /**
     * @var string Index table name - IMPORTANT: Make sure this matches db_schema.xml exactly
     */
    private string $mainTable = 'amadeco_smile_custom_entity_set_idx';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var AdapterInterface DB Connection
     */
    private AdapterInterface $connection;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var FilterableAttributeList
     */
    private FilterableAttributeList $filterableAttributeList;

    /**
     * @var EntityCollectionFactory
     */
    private EntityCollectionFactory $entityCollectionFactory;

    /**
     * @var MetadataPool Used to get entity metadata like link field
     */
    private MetadataPool $metadataPool;

    /**
     * @var string[] Attributes to index (based on frontend input type)
     */
    private const INDEXABLE_INPUT_TYPES = ['select', 'multiselect', 'boolean'];

    /**
     * @var array Map backend_type to EAV table suffix
     */
    private const BACKEND_TABLE_MAP = [
        'int' => 'int',
        'varchar' => 'varchar',
        'text' => 'text',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
    ];

    /**
     * @var int Batch size for processing entities
     */
    private const BATCH_SIZE = 500;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param FilterableAttributeList $filterableAttributeList
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        FilterableAttributeList $filterableAttributeList,
        EntityCollectionFactory $entityCollectionFactory,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->mainTable = $resourceConnection->getTableName($this->mainTable);

        $this->storeManager = $storeManager;
        $this->filterableAttributeList = $filterableAttributeList;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Regenerate the index for all entities and stores.
     *
     * This method will truncate the index table and rebuild it for all active
     * custom entities and their filterable attributes across all stores.
     *
     * @return void
     * @throws LocalizedException
     */
    public function reindexAll(): void
    {
        try {
            if (!$this->connection->isTableExists($this->mainTable)) {
                throw new LocalizedException(__('Index table does not exist: %1', $this->mainTable));
            }

            $this->connection->truncateTable($this->mainTable);

            $stores = $this->storeManager->getStores();
            $attributes = $this->getIndexableAttributes();

            if (empty($attributes)) {
                return;
            }

            $this->connection->beginTransaction();

            try {
                foreach ($stores as $store) {
                    $storeId = (int)$store->getId();
                    $entityIds = $this->getAllEntityIds();

                    if (empty($entityIds)) {
                        continue;
                    }

                    $this->processEntityBatches($entityIds, $attributes, $storeId);
                }

                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw new LocalizedException(
                    __('Failed to reindex all entities: %1', $e->getMessage()),
                    $e
                );
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('An error occurred during full reindexing: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * Process entity batches for indexing.
     *
     * @param array $entityIds Entity IDs to process
     * @param array $attributes Attributes to index
     * @param int $storeId Current store ID
     * @return void
     */
    private function processEntityBatches(array $entityIds, array $attributes, int $storeId): void
    {
        $batches = array_chunk($entityIds, self::BATCH_SIZE);
        foreach ($batches as $batchIds) {
            $indexedData = $this->fetchIndexData($storeId, $attributes, $batchIds);
            $this->insertIndexData($indexedData);
        }
    }

    /**
     * Get all active entity IDs to be indexed.
     *
     * @return array
     */
    private function getAllEntityIds(): array
    {
        $collection = $this->entityCollectionFactory->create();
        $collection->addAttributeToSelect('entity_id');
        $collection->addAttributeToFilter('is_active', 1);

        return $collection->getAllIds();
    }

    /**
     * Reindex specific entity rows.
     *
     * @param int[] $entityIds
     * @return void
     * @throws LocalizedException
     */
    public function reindexRows(array $entityIds): void
    {
        if (empty($entityIds)) {
            return;
        }

        try {
            $this->connection->delete($this->mainTable, ['entity_id IN (?)' => $entityIds]);

            $stores = $this->storeManager->getStores();
            $attributes = $this->getIndexableAttributes();

            if (empty($attributes)) {
                return;
            }

            $this->connection->beginTransaction();

            try {
                foreach ($stores as $store) {
                    $storeId = (int)$store->getId();
                    $indexedData = $this->fetchIndexData($storeId, $attributes, $entityIds);
                    $this->insertIndexData($indexedData);
                }

                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw new LocalizedException(
                    __('Failed to reindex specific entities: %1', $e->getMessage()),
                    $e
                );
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('An error occurred during partial reindexing: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * Fetch indexable data for given store, attributes, and entity IDs.
     *
     * @param int $storeId Store ID to index for
     * @param \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface[] $attributes Attributes to index
     * @param int[] $entityIds Entity IDs to index
     * @return array Structure: [ ['entity_id' => ..., 'attribute_id' => ..., 'store_id' => ..., 'value' => ...], ... ]
     * @throws LocalizedException
     */
    private function fetchIndexData(int $storeId, array $attributes, array $entityIds): array
    {
        $indexData = [];
        $entityTable = $this->resourceConnection->getTableName('smile_custom_entity');
        $linkField = $this->getEntityLinkField();

        foreach ($attributes as $attribute) {
            $attributeId = (int)$attribute->getId();
            $backendType = $attribute->getBackendType();

            if (!isset(self::BACKEND_TABLE_MAP[$backendType])) {
                continue;
            }

            $attributeTableSuffix = self::BACKEND_TABLE_MAP[$backendType];
            $attributeTable = $this->resourceConnection->getTableName('smile_custom_entity_' . $attributeTableSuffix);

            if (!$this->connection->isTableExists($attributeTable)) {
                continue;
            }

            // Build the query
            $select = $this->connection->select();
            $select->from(['e' => $entityTable], ['entity_id'])
                ->joinInner(
                    ['ea' => $attributeTable],
                    "e.{$linkField} = ea.{$linkField}",
                    [
                        'value' => 'ea.value'
                    ]
                )
                ->where('ea.attribute_id = ?', $attributeId)
                ->where('ea.store_id IN (?)', [0, $storeId])
                ->where('e.entity_id IN (?)', $entityIds);

            $rows = $this->connection->fetchAll($select);

            if (empty($rows)) {
                continue;
            }

            $this->processAttributeRows($rows, $attribute, $attributeId, $storeId, $indexData);
        }

        return $indexData;
    }

    /**
     * Process attribute rows and add to index data.
     *
     * @param array $rows The query result rows
     * @param \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface $attribute The attribute being processed
     * @param int $attributeId Attribute ID
     * @param int $storeId Store ID
     * @param array &$indexData Reference to the index data array to fill
     * @return void
     */
    private function processAttributeRows(
        array $rows,
        $attribute,
        int $attributeId,
        int $storeId,
        array &$indexData
    ): void {
        if ($attribute->getFrontendInput() === 'multiselect') {
            $this->processMultiselectAttributeRows($rows, $attributeId, $storeId, $indexData);
        } else {
            $this->processRegularAttributeRows($rows, $attributeId, $storeId, $indexData);
        }
    }

    /**
     * Process multiselect attribute rows.
     *
     * @param array $rows The query result rows
     * @param int $attributeId Attribute ID
     * @param int $storeId Store ID
     * @param array &$indexData Reference to the index data array to fill
     * @return void
     */
    private function processMultiselectAttributeRows(
        array $rows,
        int $attributeId,
        int $storeId,
        array &$indexData
    ): void {
        foreach ($rows as $row) {
            if (empty($row['value'])) {
                continue;
            }

            $values = explode(',', (string)$row['value']);
            foreach ($values as $singleValue) {
                if (!empty($singleValue)) {
                    $indexData[] = [
                        'entity_id' => $row['entity_id'],
                        'attribute_id' => $attributeId,
                        'store_id' => $storeId,
                        'value' => trim($singleValue),
                    ];
                }
            }
        }
    }

    /**
     * Process regular attribute rows.
     *
     * @param array $rows The query result rows
     * @param int $attributeId Attribute ID
     * @param int $storeId Store ID
     * @param array &$indexData Reference to the index data array to fill
     * @return void
     */
    private function processRegularAttributeRows(
        array $rows,
        int $attributeId,
        int $storeId,
        array &$indexData
    ): void {
        foreach ($rows as $row) {
            if ($row['value'] !== null && $row['value'] !== '') {
                $indexData[] = [
                    'entity_id' => $row['entity_id'],
                    'attribute_id' => $attributeId,
                    'store_id' => $storeId,
                    'value' => $row['value'],
                ];
            }
        }
    }

    /**
     * Get entity link field (usually entity_id)
     *
     * @return string
     */
    private function getEntityLinkField(): string
    {
        try {
            return $this->metadataPool->getMetadata(CustomEntityInterface::class)->getLinkField();
        } catch (\Exception $e) {
            return 'entity_id';
        }
    }

    /**
     * Get attributes that should be indexed.
     *
     * @return \Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface[]
     */
    private function getIndexableAttributes(): array
    {
        return $this->filterableAttributeList->getAllIndexableAttributes();
    }

    /**
     * Insert collected data into the index table.
     *
     * @param array $indexData
     * @return void
     */
    private function insertIndexData(array $indexData): void
    {
        if (empty($indexData)) {
            return;
        }

        $batches = array_chunk($indexData, self::BATCH_SIZE);

        foreach ($batches as $batch) {
            $dataToInsert = [];

            foreach ($batch as $row) {
                if (isset($row['entity_id'], $row['attribute_id'], $row['store_id'], $row['value'])) {
                    if ($row['value'] === null || $row['value'] === '') {
                        continue;
                    }

                    $dataToInsert[] = [
                        'entity_id' => (int)$row['entity_id'],
                        'attribute_id' => (int)$row['attribute_id'],
                        'store_id' => (int)$row['store_id'],
                        'value' => $row['value']
                    ];
                }
            }

            if (!empty($dataToInsert)) {
                $this->connection->insertMultiple($this->mainTable, $dataToInsert);
            }
        }
    }
}