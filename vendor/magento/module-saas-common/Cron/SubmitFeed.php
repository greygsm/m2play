<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Cron;

use Magento\DataExporter\Status\ExportStatusCodeProvider;
use Magento\SaaSCommon\Model\Exception\UnableSendData;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Module\ModuleList;
use Magento\SaaSCommon\Model\FeedRegistry;
use Magento\SaaSCommon\Model\Http\Command\SubmitFeed as HttpCommandSubmitFeed;
use Magento\SaaSCommon\Model\ResyncManagerPool;
use Magento\ServicesConnector\Exception\PrivateKeySignException;
use Magento\ServicesConnector\Model\Environment;
use Magento\SaaSCommon\Model\Logging\SaaSExportLoggerInterface as LoggerInterface;

/**
 * Class to execute submitting data feed
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubmitFeed implements SubmitFeedInterface
{
    public const ENVIRONMENT_CONFIG_PATH = 'magento_saas/environment';

    /**
     * @var HttpCommandSubmitFeed
     */
    private HttpCommandSubmitFeed $submitFeed;

    /**
     * @var ModuleList
     */
    private ModuleList $moduleList;

    /**
     * @var FeedPool
     */
    private FeedPool $feedPool;

    /**
     * @var FlagManager
     */
    private FlagManager $flagManager;

    /**
     * @var FeedRegistry
     */
    private FeedRegistry $feedRegistry;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var string
     */
    private string $feedName;

    /**
     * @var string
     */
    private string $feedSyncFlag;

    /**
     * @var int
     */
    private static int $iterations = 5;

    /**
     * Number of feed items send in one request
     *
     * @var int
     */
    private int $itemsPerRequest;

    private ResyncManagerPool $resyncManagerPool;

    /**
     * @param FeedPool $feedPool
     * @param HttpCommandSubmitFeed $submitFeed
     * @param ModuleList $moduleList
     * @param FlagManager $flagManager
     * @param FeedRegistry $feedRegistry
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $config
     * @param string $feedName
     * @param string $feedSyncFlag
     * @param ResyncManagerPool $resyncManagerPool
     * @param int $itemsPerRequest
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FeedPool $feedPool,
        HttpCommandSubmitFeed $submitFeed,
        ModuleList $moduleList,
        FlagManager $flagManager,
        FeedRegistry $feedRegistry,
        LoggerInterface $logger,
        ScopeConfigInterface $config,
        string $feedName,
        string $feedSyncFlag,
        ResyncManagerPool $resyncManagerPool,
        int $itemsPerRequest = 100
    ) {
        $this->feedPool = $feedPool;
        $this->submitFeed = $submitFeed;
        $this->moduleList = $moduleList;
        $this->flagManager = $flagManager;
        $this->feedRegistry = $feedRegistry;
        $this->logger = $logger;
        $this->config = $config;
        $this->feedName = $feedName;
        $this->feedSyncFlag = $feedSyncFlag;
        $this->itemsPerRequest = $itemsPerRequest;

        $this->resyncManagerPool = $resyncManagerPool;
    }

    /**
     * Submit feed data
     *
     * @param array $data
     * @return bool
     * @throws UnableSendData|PrivateKeySignException
     */
    public function submitFeed(array $data) : bool
    {
        $chunks = array_chunk($data['feed'], $this->itemsPerRequest);
        foreach ($chunks as $chunk) {
            $filteredData = $this->feedRegistry->filter($chunk);
            if (!empty($filteredData)) {
                $result = $this->submitFeed->execute(
                    $this->feedName,
                    $filteredData
                );
                if (!$result->getStatus()->isSuccess()) {
                    return false;
                } else {
                    $this->feedRegistry->registerFeed($filteredData);
                }
            }
        }
        return true;
    }

    /**
     * Iteration of data submission
     *
     * @throws \Zend_Db_Statement_Exception
     * TODO: refactor in the following way:
     * if flag "resubmit_failed_feeds" exist run cron job:
     * - iterate only for items marked as "failed"
     */
    private function iteration()
    {
        $result = true;
        $lastSyncTimestamp = $this->flagManager->getFlagData($this->feedSyncFlag);
        $feed = $this->feedPool->getFeed($this->feedName);
        $metadata = $feed->getFeedMetadata();
        try {
            if ($metadata->isExportImmediately()) {
                $data = $feed->getFeedSince(
                    $lastSyncTimestamp ?: '1',
                    ExportStatusCodeProvider::NON_RETRYABLE_HTTP_STATUS_CODE
                );
                $feed = $data['feed'];
                if (empty($feed)) {
                    return $result;
                }
                // TODO: some feeds (e.g. variants) requires custom field selected instead of "feed identity"
                $ids = \array_unique(array_column($feed, $metadata->getFeedIdentity()));
                $this->resyncManagerPool->getResyncManager($metadata->getFeedName())->regenerateFeedDataByIds($ids);
                $this->flagManager->saveFlag($this->feedSyncFlag, $data['recentTimestamp']);
            } else {
                $data = $feed->getFeedSince($lastSyncTimestamp ?: '1');
                if ($data['recentTimestamp'] !== null) {
                    $result = $this->submitFeed($data);
                    if ($result) {
                        $this->flagManager->saveFlag($this->feedSyncFlag, $data['recentTimestamp']);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
        return $result;
    }

    /**
     * Execute feed data submission
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $environment = $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH);
        $key = $this->config->getValue(str_replace('{env}', $environment, Environment::API_KEY_PATH));
        if ($key) {
            for ($i=0; $i < self::$iterations; $i++) {
                $this->iteration();
            }
        }
    }
}
