<?php
declare(strict_types = 1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 *
 * @author marcel.hauri@staempfli.com
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Model\UrlRewrite\Entity;

use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Category tree factory
     *
     * @var TreeFactory
     */
    private $categoryTreeFactory;

    /**
     * Category constructor.
     *
     * @param UrlRewriteInterface $urlRewrite
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param StoreManagerInterface $storeManager
     * @param TreeFactory $categoryTreeFactory
     */
    public function __construct(
        UrlRewriteInterface $urlRewrite,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        StoreManagerInterface $storeManager,
        TreeFactory $categoryTreeFactory
    ) {
        $this->urlRewrite = $urlRewrite;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->storeManager = $storeManager;
        $this->categoryTreeFactory = $categoryTreeFactory;
    }

    /**
     * Rebuild categories' urls.
     * Hidden and disabled categories are included.
     *
     * @param int $storeId
     * @param array $arguments
     *
     * @return void
     */
    public function rebuild(int $storeId, array $arguments = [])
    {
        $store = $this->storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();

        $categoryCollection = $this->getCategories($rootCategoryId);
        $categoryCollection->setStoreId($storeId);
        $categoryCollection->addAttributeToSelect(
            [
                'url_path',
                'url_key',
            ],
            true
        );

        if ($arguments) {
            $categoryCollection->addFieldToFilter('entity_id', ['in' => $arguments]);
        }

        $this->urlRewrite
            ->setStoreId($storeId)
            ->setEntity(CategoryUrlRewriteGenerator::ENTITY_TYPE)
            ->setRewriteGenerator($this->categoryUrlRewriteGenerator)
            ->setCollection($categoryCollection)
            ->rebuild();
    }

    /**
     * Retrieve categories.
     *
     * @param integer $parent
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getCategories($parent)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Tree $tree */
        $tree = $this->categoryTreeFactory->create();
        $tree->loadNode($parent)->loadChildren(0)->getChildren();
        $tree->addCollectionData(null, false, $parent, true, false);

        return $tree->getCollection();
    }
}
