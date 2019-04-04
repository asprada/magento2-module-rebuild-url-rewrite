<?php
declare(strict_types = 1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 *
 * @author marcel.hauri@staempfli.com
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Model\UrlRewrite\Entity;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Staempfli\RebuildUrlRewrite\Model\UrlRewrite\UrlRewriteEntityInterface;
use Staempfli\RebuildUrlRewrite\Model\UrlRewriteInterface;

/**
 * Class Category
 */
class Category implements UrlRewriteEntityInterface
{
    /**
     * @var UrlRewriteInterface
     */
    private $urlRewrite;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Category constructor.
     *
     * @param UrlRewriteInterface $urlRewrite
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param CategoryResource $categoryResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlRewriteInterface $urlRewrite,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        CategoryResource $categoryResource,
        StoreManagerInterface $storeManager
    ) {
        $this->urlRewrite = $urlRewrite;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Rebuild categories' urls.
     *
     * @param int $storeId
     * @param array $arguments
     *
     * @return void
     */
    public function rebuild(int $storeId, array $arguments = [])
    {
        $store = $this->storeManager->getStore($storeId);
        $parent = $store->getRootCategoryId();

        $categoryCollection = $this->categoryResource->getCategories($parent, $recursionLevel = 0, false, true);
        $categoryCollection->setStoreId($storeId);
        $categoryCollection->addAttributeToSelect(
            [
                'url_path',
                'url_key',
            ]
        );

        if ($arguments) {
            $this->categoryCollection->addFieldToFilter('entity_id', ['in' => $arguments]);
        }

        $this->urlRewrite
            ->setStoreId($storeId)
            ->setEntity(CategoryUrlRewriteGenerator::ENTITY_TYPE)
            ->setRewriteGenerator($this->categoryUrlRewriteGenerator)
            ->setCollection($this->categoryCollection)
            ->rebuild();
    }
}
