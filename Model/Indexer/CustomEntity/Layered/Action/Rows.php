<?php
/**
 * Copyright © Amadeco. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Indexer\CustomEntity\Layered\Action;

use Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\Indexer\Fulltext as ResourceIndexer;

/**
 * Class Rows reindex action
 */
class Rows
{
    /**
     * @var ResourceIndexer
     */
    private $resourceIndexer;

    /**
     * @param ResourceIndexer $resourceIndexer
     */
    public function __construct(
        ResourceIndexer $resourceIndexer
    ) {
        $this->resourceIndexer = $resourceIndexer;
    }

    /**
     * Execute partial indexation by entities IDs
     *
     * @param array $ids
     * @param bool $fromScheduled Whether indexation was triggered from scheduled task
     * @return void
     */
    public function execute(array $ids, bool $fromScheduled = false): void
    {
        if (!empty($ids)) {
            $this->resourceIndexer->reindexRows($ids);
        }
    }
}