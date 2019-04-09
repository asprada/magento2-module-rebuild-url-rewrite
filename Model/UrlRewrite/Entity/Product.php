<?php
declare(strict_types = 1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 *
 * @author marcel.hauri@staempfli.com
 * @author Adam Sprada <adam.sprada@gmail.com>
 */

namespace Staempfli\RebuildUrlRewrite\Model\UrlRewrite\Entity;

use Staempfli\RebuildUrlRewrite\Model\UrlRewrite\UrlRewriteEntityInterface;
use Staempfli\RebuildUrlRewrite\Model\UrlRewriteInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

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
     * Product constructor.
     *
     * @param UrlRewriteInterface $urlRewrite
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param ProductCollection $productCollection
     */
    public function __construct(
        UrlRewriteInterface $urlRewrite,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ProductCollection $productCollection
    ) {
        $this->urlRewrite = $urlRewrite;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->productCollection = $productCollection;
    }

    /**
     * @param int $storeId
     * @param array $arguments
     *
     * @return void
     */
    public function rebuild(int $storeId, array $arguments = [])
    {
        $this->productCollection->clear();
        $this->productCollection->addStoreFilter($storeId);
        $this->productCollection->addAttributeToSelect(
            [
                'url_path',
                'url_key',
            ],
            true
        );

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
}
