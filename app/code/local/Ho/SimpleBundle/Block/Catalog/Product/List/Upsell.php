<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    ${Namespace}
 * @package     ${Namespace}_${Module}
 * @copyright   Copyright © 2014 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Paul Hachmang – H&O <info@h-o.nl>
 */
 
class Ho_SimpleBundle_Block_Catalog_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell {

    public function getLoadedProductCollection() {
        return $this->getItemCollection();
    }

    protected function _prepareData()
    {
        $product = Mage::registry('product');
        /* @var $product Mage_Catalog_Model_Product */
        $this->_itemCollection = $product->getUpSellProductCollection()
            ->setPositionOrder()
            ->addStoreFilter()
        ;
        if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
                Mage::getSingleton('checkout/session')->getQuoteId()
            );

            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
//        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

        if ($this->getItemLimit('upsell') > 0) {
            $this->_itemCollection->setPageSize($this->getItemLimit('upsell'));
        }

        if ($this->getData('show_bundles')) {
            $this->_itemCollection->addAttributeToFilter('type_id', array('eq' => 'bundle'));
            /* Updating collection with desired items */
            Mage::dispatchEvent('catalog_product_upsell', array(
                'product'       => $product,
                'collection'    => $this->_itemCollection,
                'limit'         => $this->getItemLimit()
            ));
        } else {
            $this->_itemCollection->addAttributeToFilter('type_id', array('neq' => 'bundle'));
        }

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }
}
