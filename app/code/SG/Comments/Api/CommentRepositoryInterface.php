<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SG\Comments\Api\Data\CommentInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface CommentRepositoryInterface
{
    /**
     * Save Comment
     *
     * @param CommentInterface $comment
     * @return CommentInterface
     * @throws LocalizedException
     */
    public function save(
        CommentInterface $comment
    ): CommentInterface;

    /**
     * Retrieve Comment
     *
     * @param string $commentId
     * @return CommentInterface
     * @throws LocalizedException
     */
    public function get(string $commentId): CommentInterface;

    /**
     * Retrieve Comment matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface;

    /**
     * Delete Comment
     *
     * @param CommentInterface $comment
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        CommentInterface $comment
    ): bool;

    /**
     * Delete Comment by ID
     *
     * @param string $commentId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $commentId): bool;

    /**
     * Delete Comments by array of IDs
     *
     * @param array $ids
     * @return int
     * @throws LocalizedException
     */
    public function deleteByIds(array $ids): int;
}
