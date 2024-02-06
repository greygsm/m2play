<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Model;

use Magento\Framework\Model\AbstractModel;
use SG\Comments\Api\Data\CommentInterface;

class Comment extends AbstractModel implements CommentInterface
{

    /**
     * @inheritDoc
     */
    public function _construct(): void
    {
        $this->_init(\SG\Comments\Model\ResourceModel\Comment::class);
    }

    /**
     * @inheritDoc
     */
    public function getCommentId(): ?string
    {
        return $this->getData(self::COMMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCommentId(string $commentId): CommentInterface
    {
        return $this->setData(self::COMMENT_ID, $commentId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): CommentInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?string
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(?string $customerId): CommentInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail(string $email): CommentInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): CommentInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage(string $message): CommentInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): string
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId(string $productId): CommentInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
