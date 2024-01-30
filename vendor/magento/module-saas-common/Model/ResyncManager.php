<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Model;

use Exception;
use Magento\DataExporter\Model\FeedInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\FlagManager;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\SaaSCommon\Cron\SubmitFeedInterface;
use Magento\SaaSCommon\Model\Exception\UnableSendData;
use Magento\Framework\Indexer\ActionInterface as IndexerActionFeed;

/**
 * Manager for SaaS feed re-sync functions
 *
 * @api
 */
class ResyncManager
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SubmitFeedInterface
     */
    private $submitFeed;

    /**
     * @var FeedInterface
     */
    private $feedInterface;

    /**
     * @var string
     */
    private $flagName;

    /**
     * @var string
     */
    private $indexerName;

    /**
     * @var string
     */
    private $registryTableName;

    /**
     * @param IndexerActionFeed $feedIndexer
     * @param FlagManager $flagManager
     * @param IndexerRegistry $indexerRegistry
     * @param SubmitFeedInterface $submitFeed
     * @param ResourceConnection $resourceConnection
     * @param FeedInterface $feedInterface
     * @param string $flagName
     * @param string $indexerName
     * @param string $registryTableName
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        IndexerActionFeed $feedIndexer,
        FlagManager $flagManager,
        IndexerRegistry $indexerRegistry,
        SubmitFeedInterface $submitFeed,
        ResourceConnection $resourceConnection,
        FeedInterface $feedInterface,
        string $flagName,
        string $indexerName,
        string $registryTableName
    ) {
        $this->flagManager = $flagManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->submitFeed = $submitFeed;
        $this->resourceConnection = $resourceConnection;
        $this->feedInterface = $feedInterface;
        $this->flagName = $flagName;
        $this->indexerName = $indexerName;
        $this->registryTableName = $registryTableName;
    }

    /**
     * Execute full SaaS feed data re-generate and re-submit
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     */
    public function executeFullResync(): void
    {
        $this->resetIndexedData();
        $this->resetSubmittedData();
        $this->regenerateFeedData();
        $this->submitAllToFeed();
    }

    /**
     * Execute SaaS feed data re-submit only
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function executeResubmitOnly(): void
    {
        if ($this->isImmediateExport()) {
            // index will not be truncated
            $this->regenerateFeedData();
            $this->submitAllToFeed();
        } else {
            $this->resetSubmittedData();
            $this->submitAllToFeed();
        }
    }

    /**
     * Reset SaaS indexed feed data in order to re-generate
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetIndexedData(): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->invalidate();
    }

    /**
     * Reset SaaS submitted feed data in order to re-send
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function resetSubmittedData(): void
    {
        if ($this->isImmediateExport()) {
            $this->truncateIndexTable();
        }
        $connection = $this->resourceConnection->getConnection();
        $registryTable = $this->resourceConnection->getTableName($this->registryTableName);
        $connection->truncateTable($registryTable);
        $this->flagManager->deleteFlag($this->flagName);
    }

    /**
     * Re-index SaaS feed data
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function regenerateFeedData(): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->reindexAll();
    }

    /**
     * Regenerate feed data by ids
     *
     * @param array $ids
     * @return void
     */
    public function regenerateFeedDataByIds(array $ids): void
    {
        $indexer = $this->indexerRegistry->get($this->indexerName);
        $indexer->reindexList($ids);
    }

    /**
     * Truncates feed index table
     */
    public function truncateIndexTable(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $metadata = $this->feedInterface->getFeedMetadata();
        $feedTable = $this->resourceConnection->getTableName($metadata->getFeedTableName());
        $connection->truncateTable($feedTable);
    }

    /**
     * Submit all items to feed
     *
     * @throws \Zend_Db_Statement_Exception
     * @throws UnableSendData
     * @throws Exception
     */
    public function submitAllToFeed(): void
    {
        if ($this->isImmediateExport()) {
            // data already submitted right after collecting. @see \Magento\SaaSCommon\Model\ExportFeed::export
            return;
        }
        $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
        $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ?: '1');
        while ($data['recentTimestamp'] !== null) {
            $result = $this->submitFeed->submitFeed($data);
            if ($result) {
                $this->flagManager->saveFlag($this->flagName, $data['recentTimestamp']);
            } else {
                throw new \RuntimeException('There is an error during feed submit action.');
            }
            $lastSyncTimestamp = $this->flagManager->getFlagData($this->flagName);
            $data = $this->feedInterface->getFeedSince($lastSyncTimestamp ?: '1');
        }
    }

    /**
     * Check is immediate export
     *
     * @return bool
     */
    private function isImmediateExport(): bool
    {
        return $this->feedInterface->getFeedMetadata()->isExportImmediately();
    }
}
