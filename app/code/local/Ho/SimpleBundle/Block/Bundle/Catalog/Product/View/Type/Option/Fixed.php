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
