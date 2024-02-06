<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Api\Data;

interface CommentInterface
{
    const MESSAGE = 'message';
    const EMAIL = 'email';
    const NAME = 'name';
    const CREATED_AT = 'created_at';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const COMMENT_ID = 'comment_id';

    /**
     * Get comment_id
     *
     * @return string|null
     */
    public function getCommentId(): ?string;

    /**
     * Set comment_id
     *
     * @param string $commentId
     * @return CommentInterface
     */
    public function setCommentId(string $commentId): CommentInterface;

    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created_at
     *
     * @param string|null $createdAt
     * @return CommentInterface
     */
    public function setCreatedAt(?string $createdAt): CommentInterface;

    /**
     * Get customer_id
     *
     * @return string|null
     */
    public function getCustomerId(): ?string;

    /**
     * Set customer_id
     *
     * @param string|null $customerId
     * @return CommentInterface
     */
    public function setCustomerId(?string $customerId): CommentInterface;

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Set email
     *
     * @param string $email
     * @return CommentInterface
     */
    public function setEmail(string $email): CommentInterface;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set name
     *
     * @param string $name
     * @return CommentInterface
     */
    public function setName(string $name): CommentInterface;

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Set message
     *
     * @param string $message
     * @return CommentInterface
     */
    public function setMessage(string $message): CommentInterface;

    /**
     * Get product_id
     *
     * @return string
     */
    public function getProductId(): string;

    /**
     * Set product_id
     *
     * @param string $productId
     * @return CommentInterface
     */
    public function setProductId(string $productId): CommentInterface;
}
