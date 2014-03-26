<?php

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

        $product->setTypeHasOptions(false);
        $product->setTypeHasRequiredOptions(false);
        $product->canAffectOptions(true);

        $selections = $product->getBundleSelectionsData();
        $options = $product->getBundleOptionsData();
        if (($selections && !empty($selections) && $options) === false) {
            return $this;
        }

        foreach ($options as $option) {
            if (empty($option['delete']) || 1 != (int)$option['delete']) {
                $product->setTypeHasOptions(true);
                if (1 == (int)$option['required']
                        // this makes a normal add to cart button, doesn't get redirected to the product page.
                        && (isset($option['type']) && $option['type'] == self::OPTION_TYPE_FIXED) === FALSE) {
                    $product->setTypeHasRequiredOptions(true);
                    break;
                }
            }
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
