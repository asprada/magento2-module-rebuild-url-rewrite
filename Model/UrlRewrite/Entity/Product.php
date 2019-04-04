<?php
declare(strict_types = 1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 *
 * @author marcel.hauri@staempfli.com
 */

namespace Staempfli\RebuildUrlRewrite\Model\UrlRewrite\Entity;

use Staempfli\RebuildUrlRewrite\Model\UrlRewrite\UrlRewriteEntityInterface;
use Staempfli\RebuildUrlRewrite\Model\UrlRewriteInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Product
 */
class Product implements UrlRewriteEntityInterface
{
    /**
     * @var UrlRewriteInterface
     */
    private $urlRewrite;

    /**
     * @var ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        UrlRewriteInterface $urlRewrite,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ProductCollection $productCollection,

        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->urlRewrite = $urlRewrite;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->productCollection = $productCollection;

        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    public function rebuild(int $storeId, array $arguments = [])
    {
        $this->productCollection->clear();
        $this->productCollection->setStoreId($storeId);
        $this->productCollection->addAttributeToSelect(
            [
                'url_path',
                'url_key',
            ]
        );

        $store = $this->storeManager->getStore($storeId);
        $rootCategoryId = (int) $store->getRootCategoryId();

        $category = $this->getCategoryById($rootCategoryId, $storeId);
        $categoryChildren = $category->getChildren();
        $categoryChildren = explode(",", $categoryChildren);

        $this->productCollection->addCategoriesFilter(['in' => $categoryChildren]);

        if ($arguments) {
            $this->productCollection->addFieldToFilter('entity_id', ['in' => $arguments]);
        }

        $this->urlRewrite
            ->setStoreId($storeId)
            ->setEntity(ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->setRewriteGenerator($this->productUrlRewriteGenerator)
            ->setCollection($this->productCollection)
            ->rebuild();
    }

    /**
     * Get category by Id.
     *
     * @param int $categoryId
     * @param int $storeId
     *
     * @return Category
     */
    protected function getCategoryById(int $categoryId, int $storeId)
    {
        return $this->categoryRepository->get($categoryId, $storeId);
    }
}
