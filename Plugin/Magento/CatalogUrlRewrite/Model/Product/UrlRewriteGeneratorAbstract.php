<?php
/**
 * Copyright © 2019 Adam Sprada. All rights reserved.
 *
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Plugin\Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class UrlRewriteGeneratorAbstract
 */
abstract class UrlRewriteGeneratorAbstract
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
