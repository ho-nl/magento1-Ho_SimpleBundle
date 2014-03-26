<?php

class Ho_SimpleBundle_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isBundleProductSimple(Mage_Catalog_Model_Product $product) {

        return true;
    }


    /**
     * Returns product price block html
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getOriginalPriceHtml(Mage_Catalog_Model_Product $product)
    {
        $price = $product->getPrice();
        return $this->_renderPrice($product, $price);
    }

    public function getDiscountPriceHtml(Mage_Catalog_Model_Product $product) {
        $price = $product->getPrice() - $product->getFinalPrice();
        return $this->_renderPrice($product, $price);
    }

    public function getFinalPriceHtml(Mage_Catalog_Model_Product $product) {
        $price = $product->getFinalPrice();
        return $this->_renderPrice($product, $price);
    }

    protected function _renderPrice(Mage_Catalog_Model_Product $product, $price) {
        $store = $product->getStore();
        $convertedPrice = $store->roundPrice($store->convertPrice($price));
        $price = Mage::helper('tax')->getPrice($product, $convertedPrice);

        return Mage::helper('core')->formatPrice($price);
    }
}
