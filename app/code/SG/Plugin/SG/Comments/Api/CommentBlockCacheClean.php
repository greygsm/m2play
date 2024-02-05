<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Plugin\SG\Comments\Api;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type;
use SG\Comments\Api\CommentRepositoryInterface;
use SG\Comments\Api\Data\CommentInterface;
use SG\Comments\Block\Product\Comment as CommentBlock;

class CommentBlockCacheClean
{
    /**
     * @param CacheInterface $cache
     * @param Type $fullPageCache
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Type           $fullPageCache,
    ) {
    }

    /**
     * Clearing block cache after save
     *
     * @param CommentRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(CommentRepositoryInterface $subject, $result): CommentInterface
    {
        $this->cleanCommentBlockCache();
        return $result;
    }

    /**
     * Clearing block cache after delete
     *
     * @param CommentRepositoryInterface $subject
     * @param $result
     * @return bool
     */
    public function afterDelete(CommentRepositoryInterface $subject, $result): bool
    {
        $this->cleanCommentBlockCache();
        return $result;
    }

    /**
     * Clearing block cache after delete by id
     *
     * @param CommentRepositoryInterface $subject
     * @param $result
     * @return bool
     */
    public function afterDeleteById(CommentRepositoryInterface $subject, $result): bool
    {
        $this->cleanCommentBlockCache();
        return $result;
    }

    /**
     * Clearing block cache after delete by ids
     *
     * @param CommentRepositoryInterface $subject
     * @param $result
     * @return int
     */
    public function afterDeleteByIds(CommentRepositoryInterface $subject, $result): int
    {
        $this->cleanCommentBlockCache();
        return $result;
    }

    /**
     * Clearing block cache by tag
     *
     * @return void
     */
    private function cleanCommentBlockCache(): void
    {
        $tags = [CommentBlock::CACHE_TAG];
        $this->cache->clean($tags);
        $this->fullPageCache
            ->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }
}
