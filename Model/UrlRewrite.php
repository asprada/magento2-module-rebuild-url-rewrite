<?php
declare(strict_types = 1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 *
 * @author marcel.hauri@staempfli.com
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class UrlRewrite
 */
class UrlRewrite implements UrlRewriteInterface
{
    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @var string|null
     */
    private $entity;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection|null
     */
    private $collection;

    /**
     * @var null
     */
    private $rewriteGenerator;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UrlRewrite constructor.
     *
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(UrlPersistInterface $urlPersist, StoreManagerInterface $storeManager)
    {
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->output = new ConsoleOutput();
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @param string $entity
     *
     * @return $this
     */
    public function setEntity(string $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param $rewriteGenerator
     *
     * @return $this
     */
    public function setRewriteGenerator($rewriteGenerator)
    {
        if (!$this->validateObject($rewriteGenerator, 'generate')) {
            throw new \LogicException('Invalid Rewrite Generator!');
        }
        $this->rewriteGenerator = $rewriteGenerator;

        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     *
     * @return $this
     */
    public function setCollection(\Magento\Framework\Data\Collection $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @return void
     */
    public function rebuild()
    {
        $collection = $this->getCollection();
        $rootCategoryId = $this->getCategoryRootId($this->getStoreId());
        $progressBar = new ProgressBar($this->output, $collection->getSize());
        $progressBar->start();
        foreach ($collection as $item) {
            try {
                $progressBar->advance();
                $item->setStoreId($this->getStoreId());
                $this->deleteByEntity((int) $item->getId());
                $urls = $this->generateRewrites($item, $rootCategoryId);
                $this->urlPersist->replace($urls);
            } catch (\LogicException $e) {
                throw new \LogicException($e->getMessage());
            } catch (\Exception $e) {
                $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }
        $progressBar->finish();
        $this->output->writeln('');
    }

    /**
     * Generate rewrites for different types of input.
     *
     * @param mixed $item
     * @param int|null $rootCategoryId
     *
     * @return array
     */
    protected function generateRewrites($item, $rootCategoryId)
    {
        if ($item instanceof Category) {
            $params =
                [
                    $item,
                    false,
                    $rootCategoryId,
                ];
        } elseif ($item instanceof Product) {
            $params =
                [
                    $item,
                    $rootCategoryId,
                ];
        } else {
            $params = [$item];
        }

        return $this->getRewriteGenerator()->generate(...$params);
    }

    /**
     * Get category root ID.
     *
     * @param int $storeId
     *
     * @return int
     */
    protected function getCategoryRootId(int $storeId)
    {
        $store = $this->storeManager->getStore($storeId);

        return $store->getRootCategoryId();
    }

    /**
     * @param object $object
     * @param string $method
     *
     * @return bool
     */
    private function validateObject($object, string $method = ''): bool
    {
        return \is_object($object) && method_exists($object, $method);
    }

    private function getStoreId()
    {
        if (!$this->storeId) {
            throw new \LogicException('Store ID not set!');
        }

        return $this->storeId;
    }

    private function getEntity()
    {
        if (!$this->entity) {
            throw new \LogicException('Entity type not set!');
        }

        return $this->entity;
    }

    private function getCollection()
    {
        if (!$this->collection) {
            throw new \LogicException('Collection not set!');
        }

        return $this->collection;
    }

    private function getRewriteGenerator()
    {
        if (!$this->rewriteGenerator) {
            throw new \LogicException('URL Rewrite Generator not set!');
        }

        return $this->rewriteGenerator;
    }

    private function deleteByEntity(int $entityId)
    {
        $this->urlPersist->deleteByData(
            [
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $entityId,
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => $this->getEntity(),
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $this->getStoreId(),
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 0,
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::IS_AUTOGENERATED => 1,
            ]
        );
    }
}
