<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCommon\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool of all feed resync managers
 */
class ResyncManagerPool
{
    /**
     * @var array
     */
    private $registry;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $classMap;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $classMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $classMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->classMap = $classMap;
    }

    /**
     * Returns resync manager object
     *
     * @param string $feedName
     * @return ResyncManager
     * @throws \InvalidArgumentException
     */
    public function getResyncManager(string $feedName) : ResyncManager
    {
        if (!isset($this->classMap[$feedName])) {
            $options = implode(',', array_keys($this->classMap));
            throw new \InvalidArgumentException('Resync feed option is not available. Available feeds: ' . $options);
        }
        if (!isset($this->registry[$feedName])) {
            $this->registry[$feedName] = $this->objectManager->get($this->classMap[$feedName]);
        }
        return $this->registry[$feedName];
    }
}
