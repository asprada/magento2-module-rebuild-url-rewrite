<?php
/**
 * Copyright © 2019 Adam Sprada. All rights reserved.
 *
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Plugin\Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator as ParentCategoriesUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class CategoriesUrlRewriteGeneratorPlugin
 */
class CategoriesUrlRewriteGeneratorPlugin extends UrlRewriteGeneratorAbstract
{
    /**
     * When "Use Categories Path for Product URLs" is set to "No" then skip generating rewrites for unused paths.
     *
     * @param ParentCategoriesUrlRewriteGenerator $subject
     * @param \Closure $proceed
     * @param $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     *
     * @return UrlRewrite[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGenerate(
        ParentCategoriesUrlRewriteGenerator $subject,
        \Closure $proceed,
        $storeId,
        Product $product,
        ObjectRegistry $productCategories
    ) {
        if ($this->isSeoUseCategoryPathForProductEnabled($storeId)) {
            return $proceed($storeId, $product, $productCategories);
        }

        return [];
    }
}
