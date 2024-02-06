<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */
declare(strict_types=1);

namespace SG\Comments\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SG\Comments\Api\Data\CommentInterface;

class Comment extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('sg_comments_comment', CommentInterface::COMMENT_ID);
    }

    /**
     * Delete by provided id
     *
     * @param array $ids
     * @return int
     * @throws LocalizedException
     */
    public function deleteByIds(array $ids): int
    {
        $quotedIds = $this->getConnection()->quoteInto('IN (?)', $ids);
        return $this->getConnection()->delete(
            $this->getMainTable(),
            CommentInterface::COMMENT_ID . ' ' . $quotedIds
        );
    }
}
