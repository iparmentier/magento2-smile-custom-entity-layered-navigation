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

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Indexer\CustomEntity;

use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexMutexInterface;
use Magento\Framework\Exception\LocalizedException;
use Smile\CustomEntity\Model\CustomEntity;
use Psr\Log\LoggerInterface;

/**
 * Custom Entity Layered Navigation Indexer
 */
class Layered implements IndexerActionInterface, MviewActionInterface
{
    /**
     * Indexer ID in configuration
     */
    public const INDEXER_ID = 'amadeco_smile_custom_entity_layer_set';

    /**
     * @var Layered\Action\FullFactory
     */
    private $fullActionFactory;

    /**
     * @var Layered\Action\RowsFactory
     */
    private $rowsActionFactory;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var string
     */
    private $indexerId;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var IndexMutexInterface
     */
    private $indexMutex;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Layered\Action\FullFactory $fullActionFactory
     * @param Layered\Action\RowsFactory $rowsActionFactory
     * @param IndexerRegistry $indexerRegistry
     * @param CacheContext $cacheContext
     * @param IndexMutexInterface $indexMutex
     * @param LoggerInterface $logger
     * @param string $indexerId
     */
    public function __construct(
        Layered\Action\FullFactory $fullActionFactory,
        Layered\Action\RowsFactory $rowsActionFactory,
        IndexerRegistry $indexerRegistry,
        CacheContext $cacheContext,
        IndexMutexInterface $indexMutex,
        LoggerInterface $logger,
        string $indexerId = self::INDEXER_ID
    ) {
        $this->fullActionFactory = $fullActionFactory;
        $this->rowsActionFactory = $rowsActionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->cacheContext = $cacheContext;
        $this->indexMutex = $indexMutex;
        $this->logger = $logger;
        $this->indexerId = $indexerId;
    }

    /**
     * Execute indexer on specified entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        try {
            $this->executeAction($ids);
            $this->registerEntities($ids);
        } catch (\Exception $e) {
            $this->logger->error('Error during indexing specific entities: ' . $e->getMessage(), ['exception' => $e]);
            throw new LocalizedException(__('Could not rebuild index for specified entities: %1', $e->getMessage()), $e);
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $indexer = $this->indexerRegistry->get($this->indexerId);

        if ($indexer->isScheduled()) {
            // Skip manual reindex if indexer is in scheduled mode
            return;
        }

        try {
            $this->indexMutex->execute(
                $this->indexerId,
                function () {
                    $this->fullActionFactory->create()->execute();
                    $this->registerTags();
                }
            );
        } catch (\Exception $e) {
            $this->logger->error('Error during full reindex: ' . $e->getMessage(), ['exception' => $e]);
            throw new LocalizedException(__('Could not rebuild index for all entities: %1', $e->getMessage()), $e);
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        try {
            $this->executeAction($ids);
        } catch (\Exception $e) {
            $this->logger->error('Error during executing mview by ID list: ' . $e->getMessage(), [
                'exception' => $e,
                'ids' => implode(', ', $ids)
            ]);
            throw new LocalizedException(__('Could not rebuild index for the specified entities: %1', $e->getMessage()), $e);
        }
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * Execute indexer action for specific entities
     *
     * @param int[] $ids
     * @return $this
     */
    private function executeAction(array $ids)
    {
        if (empty($ids)) {
            return $this;
        }

        $ids = array_unique($ids);
        $indexer = $this->indexerRegistry->get($this->indexerId);

        if (!$indexer->isScheduled()) {
            $this->rowsActionFactory->create()->execute($ids);
        }

        return $this;
    }

    /**
     * Add entities to cache context
     *
     * @param int[] $ids
     * @return void
     */
    private function registerEntities(array $ids)
    {
        $this->cacheContext->registerEntities(CustomEntity::CACHE_TAG, $ids);
    }

    /**
     * Add tags to cache context
     *
     * @return void
     */
    private function registerTags()
    {
        $this->cacheContext->registerTags([CustomEntity::CACHE_TAG]);
    }
}