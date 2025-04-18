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