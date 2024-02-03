<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SG\Comments\Api\CommentRepositoryInterface;
use SG\Comments\Api\Data\CommentInterface;
use SG\Comments\Api\Data\CommentInterfaceFactory;
use SG\Comments\Api\Data\CommentSearchResultsInterfaceFactory;
use SG\Comments\Model\ResourceModel\Comment as ResourceComment;
use SG\Comments\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

class CommentRepository implements CommentRepositoryInterface
{

    /**
     * @param ResourceComment $resource
     * @param CommentInterfaceFactory $commentFactory
     * @param CommentCollectionFactory $commentCollectionFactory
     * @param CommentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly ResourceComment $resource,
        private readonly CommentInterfaceFactory $commentFactory,
        private readonly CommentCollectionFactory $commentCollectionFactory,
        private readonly CommentSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(CommentInterface $comment): CommentInterface
    {
        try {
            $this->resource->save($comment);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the comment: %1',
                $exception->getMessage()
            ));
        }
        return $comment;
    }

    /**
     * @inheritDoc
     */
    public function get(string $commentId): CommentInterface
    {
        $comment = $this->commentFactory->create();
        $this->resource->load($comment, $commentId);
        if (!$comment->getId()) {
            throw new NoSuchEntityException(__('Comment with id "%1" does not exist.', $commentId));
        }
        return $comment;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): SearchResultsInterface
    {
        $collection = $this->commentCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CommentInterface $comment): bool
    {
        try {
            $this->resource->delete($comment);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Comment: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(string $commentId): bool
    {
        return $this->delete($this->get($commentId));
    }

    /**
     * @inheritDoc
     */
    public function deleteByIds(array $ids): int
    {
        return $this->resource->deleteByIds($ids);
    }
}

