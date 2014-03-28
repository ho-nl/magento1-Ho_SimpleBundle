<?php
/*
 * H&O Simple Bundles
 *
 * This source file is subject to the H&O Commercial License that
 * is bundled with this package in the file LICENSE_HO.txt
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/shop/customer-service/licensing-information
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package a to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Ho
 * @package     Ho_SimpleBundle
 * @copyright   Copyright (c) H&O (www.h-o.nl)
 * @license     http://www.h-o.nl/license H&O Commercial License
 */
/**
 * Bundle option fixed type renderer
 */
class Ho_SimpleBundle_Block_Bundle_Catalog_Product_View_Type_Option_Fixed
    extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('ho/simplebundle/catalog/product/view/type/bundle/option/fixed.phtml');
    }

    public function getSelections() {
        $selections = $this->getOption()->getSelections();

        foreach ($selections as $product) {
            /** @var $product Mage_Catalog_Model_Product */
            /** @var $urlRewrite Mage_Core_Model_Url_Rewrite */
            $urlRewrite = Mage::getModel('core/url_rewrite');
            $urlRewrite->loadByIdPath('product/'.$product->getId());
            $product->setData('request_path', $urlRewrite->getRequestPath());
        }

        return $selections;
    }

}
