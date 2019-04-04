<?php
/**
 * Copyright © 2019 Adam Sprada. All rights reserved.
 *
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Plugin\Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator as ParentCurrentUrlRewritesRegenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class CurrentUrlRewritesRegeneratorPlugin
 */
class CurrentUrlRewritesRegeneratorPlugin extends UrlRewriteGeneratorAbstract
{
    /**
     * When "Use Categories Path for Product URLs" is set to "No" then skip generating rewrites for unused paths.
     *
     * @param ParentCurrentUrlRewritesRegenerator $subject
     * @param \Closure $proceed
     * @param $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param int|null $rootCategoryId
     *
     * @return UrlRewrite[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGenerate(
        ParentCurrentUrlRewritesRegenerator $subject,
        \Closure $proceed,
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        $rootCategoryId = null
    ) {
        if ($this->isSeoUseCategoryPathForProductEnabled($storeId)) {
            return $proceed($storeId, $product, $productCategories, $rootCategoryId);
        }

        return [];
    }

    /**
     * When "Use Categories Path for Product URLs" is set to "No" then skip generating rewrites for unused paths.
     *
     * @param ParentCurrentUrlRewritesRegenerator $subject
     * @param \Closure $proceed
     * @param $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param null $rootCategoryId
     *
     * @return UrlRewrite[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGenerateAnchor(
        ParentCurrentUrlRewritesRegenerator $subject,
        \Closure $proceed,
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        $rootCategoryId = null
    ) {
        if ($this->isSeoUseCategoryPathForProductEnabled($storeId)) {
            return $proceed($storeId, $product, $productCategories, $rootCategoryId);
        }

        return [];
    }
}
