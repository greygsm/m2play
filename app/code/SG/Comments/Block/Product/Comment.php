<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
namespace SG\Comments\Block\Product;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use SG\Comments\Api\Data\CommentInterface;
use SG\Comments\Model\ResourceModel\Comment\Collection;
use SG\Comments\Model\ResourceModel\Comment\CollectionFactory;

/**
 * Product Comment Tab
 */
class Comment extends Template implements IdentityInterface
{
    /**
     * Cache tag value
     */
    public const CACHE_TAG = 'comment_block';

    protected ?Collection $collection = null;

    public ?string $productId = null;

    /**
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly Registry $coreRegistry,
        private readonly CollectionFactory $collectionFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProductId(): ?int
    {
        $product = $this->coreRegistry->registry('product');
        return $product ? $product->getId() : $this->getData('productId');
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle(): void
    {
        $this->setTitle(__('Comments'));
    }

    /**
     * Get Comments Collection
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()
                ->addFieldToSelect(CommentInterface::NAME)
                ->addFieldToSelect(CommentInterface::EMAIL)
                ->addFieldToSelect(CommentInterface::CREATED_AT)
                ->addFieldToSelect(CommentInterface::MESSAGE)
                ->addFieldToFilter(CommentInterface::PRODUCT_ID, $this->getProductId())
                ->setOrder(CommentInterface::CREATED_AT, \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        }
        return $this->collection;
    }

    /**
     * Get Comments items
     *
     * @return array
     * @throws \Exception
     */
    public function getItems(): array
    {
        $items = $this->getCollection()->getItems();

        return array_map(function(CommentInterface $comment){
            return [
                CommentInterface::NAME => $comment->getName(),
                CommentInterface::EMAIL => $comment->getEmail(),
                CommentInterface::CREATED_AT => (new \DateTime($comment->getData('created_at') ?: 'now'))
                    ->format('d/m/Y H:i:s'),
                CommentInterface::MESSAGE => $comment->getMessage(),
            ];
        }, $items);
    }

    /**
     * Define cache tag
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG];
    }
}
