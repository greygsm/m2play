<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CommentSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get Comment list.
     *
     * @return CommentInterface[]
     */
    public function getItems(): array;

    /**
     * Set items list.
     *
     * @param CommentInterface[] $items
     * @return $this
     */
    public function setItems(array $items): static;
}
