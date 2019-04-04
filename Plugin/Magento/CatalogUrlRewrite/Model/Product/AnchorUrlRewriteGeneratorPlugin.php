<?php
/**
 * Copyright © 2019 Adam Sprada. All rights reserved.
 *
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Plugin\Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator as ParentAnchorUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class AnchorUrlRewriteGenerator
 */
class AnchorUrlRewriteGeneratorPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CategoriesUrlRewriteGeneratorPlugin constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * When "Use Categories Path for Product URLs" is set to "No" then skip generating rewrites for unused paths.
     *
     * @param ParentAnchorUrlRewriteGenerator $subject
     * @param \Closure $proceed
     * @param $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     *
     * @return UrlRewrite[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGenerate(
        ParentAnchorUrlRewriteGenerator $subject,
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

    /**
     * Get bool value for configuration option:
     * See: Stores > Settings > Configuration > Catalog > Catalog >
     *      Search Engine Optimization > Use Categories Path for Product URLs : (Yes/No)
     *
     * @param int $storeId
     *
     * @return bool
     */
    protected function isSeoUseCategoryPathForProductEnabled(int $storeId)
    {
        return $this->scopeConfig->isSetFlag(
            ProductHelper::XML_PATH_PRODUCT_URL_USE_CATEGORY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
