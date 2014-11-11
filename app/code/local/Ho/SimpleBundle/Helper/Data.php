<?php
/**
 * Ho_SimpleBundle
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_SimpleBundle
 * @copyright   Copyright © 2014 H&O (http://www.h-o.nl/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Paul Hachmang – H&O <info@h-o.nl>
 */

class Ho_SimpleBundle_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get product html block
     *
     * @param string                     $mode
     * @param Mage_Catalog_Model_Product $product
     * @param array                      $data
     *
     * @return string
     */
    public function getProductHtml($mode, Mage_Catalog_Model_Product $product, $data = array())
    {
        if ($this->isModuleEnabled('Ho_Bootstrap')) {
            $renderer = Mage::helper('ho_bootstrap/list')->getProductRenderer($mode, $product->getTypeId(),$product->getAttributeSetId());
        } else {
            $renderer = array(
                'block' => 'ho_simplebundle/catalog_product_list_type_simplebundle',
                'template' => 'ho/simplebundle/catalog/product/list/type/default/simplebundle.phtml'
            );
        }

        /** @var $block Ho_Bootstrap_Block_Catalog_Product_List_Type_Default */
        $block = Mage::app()->getLayout()->createBlock($renderer['block'])
            ->setTemplate($renderer['template'])
            ->setProduct($product)
            ->addData($data);

        return $block->toHtml();
    }


    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     * @todo
     */
    public function isBundleProductSimple(Mage_Catalog_Model_Product $product) {
        if (! $product->hasData('is_bundle_simple')) {
            /** @var Ho_SimpleBundle_Model_Bundle_Product_Type $typeInstance */
            $typeInstance = $product->getTypeInstance(true);
            $optionsCollection = $typeInstance->getOptionsCollection($product);
            $optionsCollection->addFieldToFilter('type', array('neq' => 'fixed'));
            $select = $optionsCollection->getSelect();
            $select->reset('columns');
            $select->columns('type');
            $result = $optionsCollection->getConnection()->fetchCol($select);
            $product->setData('is_bundle_simple', !count($result));
        }

        return $product->getData('is_bundle_simple');
    }


    /**
     * Returns product price block html
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getOriginalPriceHtml(Mage_Catalog_Model_Product $product)
    {
        $price = $this->_getOriginalPrice($product);
        return $this->_renderPrice($product, $price);
    }

    protected function _getOriginalPrice(Mage_Catalog_Model_Product $product, $skipFirst = false)
    {
        $key = $skipFirst ? '_original_price_simple' : '_original_price';
        if (! $product->getData($key)) {
            $options = $this->getProductOptions($product);
            $originalPrice = 0;
            foreach ($options as $option) {
                if ($skipFirst) {
                    $skipFirst = false;
                    continue;
                }
                $originalPrice += $option->getFinalPrice() * $option->getSelectionQty();
            }
            $product->setData($key, $originalPrice);
        }

        return  $product->getData($key);
    }


    public function getProductOptions(Mage_Catalog_Model_Product $product)
    {
        /** @var Ho_SimpleBundle_Model_Bundle_Product_Type $typeInstance */
        $typeInstance = $product->getTypeInstance(true);
        return $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
    }

    public function getDiscountPercentageHtml(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        return $this->_renderPercentage(
            ($this->_getOriginalPrice($product, true) / 100) * $this->_getDiscountPrice($product) * 100
        );
    }

    public function getDiscountPriceHtml(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        return $this->_renderPrice($product, $this->_getDiscountPrice($product));
    }

    protected function _getDiscountPrice(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        return ($this->_getOriginalPrice($product) - $product->getMaxPrice()) * $qty;
    }

    public function getMaxPriceHtml(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $price = $product->getMaxPrice() * $qty;
        return $this->_renderPrice($product, $price);
    }

    public function getFinalPriceHtml(Mage_Catalog_Model_Product $product, $qty = 1)
    {
        $price = $product->getFinalPrice() * $qty;
        return $this->_renderPrice($product, $price);
    }

    protected function _renderPercentage($percentage)
    {
        return round($percentage).'%';
    }

    protected function _renderPrice(Mage_Catalog_Model_Product $product, $price)
    {
        $store = $product->getStore();
        $convertedPrice = $store->roundPrice($store->convertPrice($price));
        $price = Mage::helper('tax')->getPrice($product, $convertedPrice);

        return Mage::helper('core')->formatPrice($price);
    }
}
