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
 * @method Ho_SimpleBundle_Block_Catalog_Product_List_Type_Simplebundle setProduct(Mage_Catalog_Model_Product $product)
 */
if (Mage::helper('core')->isModuleEnabled('Ho_Bootstrap')) {
    class Ho_SimpleBundle_Block_Catalog_Product_List_Type_Simplebundle
        extends Ho_Bootstrap_Block_Catalog_Product_List_Type_Default {}
} else {
    class Ho_SimpleBundle_Block_Catalog_Product_List_Type_Simplebundle
        extends Mage_Catalog_Block_Product_Abstract {}
}
