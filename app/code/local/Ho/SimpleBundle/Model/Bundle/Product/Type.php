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

class Ho_SimpleBundle_Model_Bundle_Product_Type extends Mage_Bundle_Model_Product_Type
{
    const OPTION_TYPE_FIXED = 'fixed';


    /**
     * Makes sure that for OPTION_TYPE_FIXED the options aren't required to enter.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return \Mage_Catalog_Model_Product_Type_Abstract
     */
    public function beforeSave($product = null)
    {
        parent::beforeSave($product);
        $product = $this->getProduct($product);

        if (! $product->getCanSaveBundleSelections()) {
            return $this;
        }


        $selections = $product->getBundleSelectionsData();
        $options = $product->getBundleOptionsData();
        if (($selections && !empty($selections) && $options) === false) {
            return $this;
        }

        $isSimpleBundle = true;
        foreach ($options as $option) {
            if (empty($option['delete']) || 1 != (int)$option['delete']) {
                if ((isset($option['type']) && $option['type'] == self::OPTION_TYPE_FIXED) === false) {
                    $isSimpleBundle = false;
                }
            }
        }

        if ($isSimpleBundle) {
            $product->setTypeHasOptions(false);
            $product->setTypeHasRequiredOptions(false);
            $product->canAffectOptions(true);
            $product->setVisibility(1); //@todo make bundle product page.
        }

        return $this;
    }




    /**
     * Allow Fixed Bundles to be added to the cart without needing to submit the add to cart form
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     * @author Paul Hachmang <paul@h-o.nl>
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $product = $this->getProduct($product);
        $options = $buyRequest->getBundleOption();

        $optionsCollection = $this->getOptionsCollection($product);
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && count($options) <= 0)
        {
            foreach ($optionsCollection->getItems() as $option)
            {
                if ($option->getType() == self::OPTION_TYPE_FIXED && $option->getRequired()
                        && !isset($options[$option->getId()]))
                {
                    $selectionData = Mage::getResourceSingleton('bundle/bundle')->getSelectionsData($product->getId());

                    foreach ($selectionData as $prod) {
                        $options[$option->getId()][] = $prod['selection_id'];
                    }
                }
            }

            //we are placing the new information in the request
            $buyRequest->setBundleOption($options);
        }

        return parent::_prepareProduct($buyRequest, $product, $processMode);
    }
}
