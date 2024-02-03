<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Model\ResourceModel\Comment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SG\Comments\Model\Comment as ModelComment;
use SG\Comments\Model\ResourceModel\Comment as ResourceComment;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'comment_id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(
            ModelComment::class,
            ResourceComment::class
        );
    }
}
