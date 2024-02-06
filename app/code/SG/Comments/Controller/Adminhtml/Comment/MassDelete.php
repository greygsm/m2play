<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */

declare(strict_types=1);

namespace SG\Comments\Controller\Adminhtml\Comment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use SG\Comments\Model\ResourceModel\Comment\CollectionFactory;
use SG\Comments\Model\CommentRepository;

class MassDelete implements HttpPostActionInterface
{

    /**
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $manager
     * @param CommentRepository $commentRepository
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly RequestInterface  $request,
        private readonly RedirectFactory   $redirectFactory,
        private readonly ManagerInterface  $manager,
        private readonly CommentRepository  $commentRepository
    ) {
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $commentsIds = $this->request->getParam('selected');
        if (!is_array($commentsIds)) {
            $this->manager->addErrorMessage(__('Please select comment(s).'));
        } else {
            try {
                $deletedCount = $this->commentRepository->deleteByIds($commentsIds);

                $this->manager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $deletedCount)
                );
            } catch (\Exception $e) {
                $this->manager->addErrorMessage(__($e->getMessage()));
            }
        }

        return $this->redirectFactory->create()->setRefererOrBaseUrl();
    }
}
