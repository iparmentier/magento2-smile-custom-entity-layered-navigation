<?php
/**
 * Copyright © Amadeco. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Model\Indexer\CustomEntity\Layered\Action;

use Amadeco\SmileCustomEntityLayeredNavigation\Model\ResourceModel\Indexer\Fulltext as ResourceIndexer;
use Psr\Log\LoggerInterface;

/**
 * Class Full reindex action
 */
class Full
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
     * Execute full indexation
     *
     * @return void
     */
    public function execute(): void
    {
        $this->resourceIndexer->reindexAll();
    }
}