<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ho_SimpleBundle_Adminhtml_Catalog_Upsell_ProductController extends Mage_Adminhtml_Controller_Action
{
    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit', 'bundle');

    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Ho_SimpleBundle');
    }

    /**
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Products'));

        $productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (Exception $e) {
                $product->setTypeId(Mage_Catalog_Model_Product_Type::DEFAULT_TYPE);
                Mage::logException($e);
            }
        }

        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
            (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                explode(",", base64_decode(urldecode($attributes)))
            );
        }

        // Required attributes of simple product for configurable creation
        if ($this->getRequest()->getParam('popup')
            && $requiredAttributes = $this->getRequest()->getParam('required')) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
            && $this->getRequest()->getParam('product')
            && !is_array($this->getRequest()->getParam('product'))
            && $this->getRequest()->getParam('id', false) === false) {

            $configProduct = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->load($this->getRequest()->getParam('product'))
                ->setTypeId($this->getRequest()->getParam('type'));

            /* @var $configProduct Mage_Catalog_Model_Product */
            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes() as $attribute) {

                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if(!$attribute->getIsUnique()
                    && $attribute->getFrontend()->getInputType()!='gallery'
                    && $attribute->getAttributeCode() != 'required_options'
                    && $attribute->getAttributeCode() != 'has_options'
                    && $attribute->getAttributeCode() != $configProduct->getIdFieldName()) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }

            $product->addData($data)
                ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $product;
    }

//    /**
//     * Create serializer block for a grid
//     *
//     * @param string $inputName
//     * @param Mage_Adminhtml_Block_Widget_Grid $gridBlock
//     * @param array $productsArray
//     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Ajax_Serializer
//     */
//    protected function _createSerializerBlock($inputName, Mage_Adminhtml_Block_Widget_Grid $gridBlock, $productsArray)
//    {
//        return $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_ajax_serializer')
//            ->setGridBlock($gridBlock)
//            ->setProducts($productsArray)
//            ->setInputElementName($inputName)
//        ;
//    }


    /**
     * Get associated grouped products grid and serializer block
     */
    public function bundleAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.upsell.config.grid')
            ->setProductsUpsellBundle($this->getRequest()->getPost('products_upsell_bundle', null));
        $this->renderLayout();
    }

    public function quickCreateAction()
    {
        $result = array();

        try {
            /* @var $parentProduct Mage_Catalog_Model_Product */
            $parentProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                ->load($this->getRequest()->getParam('id'));

            if (!$parentProduct->getId()) {
                // If invalid parent product
                $this->_redirect('*/*/');
                return;
            }

            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
                ->setAttributeSetId($parentProduct->getAttributeSetId());

            foreach ($product->getTypeInstance()->getEditableAttributes() as $attribute) {
                if ($attribute->getIsUnique()
                    || $attribute->getAttributeCode() == 'url_key'
                    || $attribute->getFrontend()->getInputType() == 'gallery'
                    || $attribute->getFrontend()->getInputType() == 'media_image'
                    || !$attribute->getIsVisible()) {
                    continue;
                }

                $product->setData(
                    $attribute->getAttributeCode(),
                    $parentProduct->getData($attribute->getAttributeCode())
                );
            }

            $bundleProductData = $this->getRequest()->getParam('bundle_product', array());
            $product->addData($bundleProductData);

            Mage::helper('adminhtml/data')->prepareFilterString($product->getProducts());

            //add the product data.
            $childProducts = Mage::helper('adminhtml/js')->decodeGridSerializedInput($product->getProducts());

            if (! $childProducts) {
                Mage::throwException($this->__('Please select products for the bundle'));
            }

            $product->setBundleOptionsData(array(array(
                  'title' => '',
                  'delete' => 0,
                  'type' =>   Ho_SimpleBundle_Model_Bundle_Product_Type::OPTION_TYPE_FIXED,
                  'required' => 1,
                  'position' => 0,
            )));
            $bundleOptionsData = array();

            //Add the current product
            $bundleOptionsData[$parentProduct->getId()] = array(
                'selection_id' => '',
                'option_id' => '',
                'delete' => 0,
                'product_id' => $parentProduct->getId(),
                'selection_price_value' => $parentProduct->getPrice(),
                'selection_price_type' => '0',
                'selection_qty' => 1,
                'selection_can_change_qty' => '0',
                'position' => 0,
                'is_default' => 1,
            );

            //sAdd the additional products
            foreach ($childProducts as $productId => $childProduct) {
                $bundleOptionsData[$productId] = array(
                    'selection_id' => '',
                    'option_id' => '',
                    'delete' => 0,
                    'product_id' => $productId,
                    'selection_price_value' => '',
                    'selection_price_type' => '0',
                    'selection_qty' => max(1, $childProduct['qty']),
                    'selection_can_change_qty' => '0',
                    'position' => $childProduct['position'] + 1,
                    'is_default' => 1,
                );
            }
            $product->setBundleSelectionsData(array($bundleOptionsData));
            $product->setWebsiteIds($parentProduct->getWebsiteIds());

            $productCollection = $product->getCollection();
            $productCollection->addFieldToFilter('entity_id', array('in'=> array_keys($bundleOptionsData)));
            $productCollection->addAttributeToSelect(array('name','price', 'special_price'));

            $prices = array();
            $name = array();
            $sku = array();
            foreach ($bundleOptionsData as $productId => $bundleOption) {
                /** @var Mage_Catalog_Model_Product $childProduct */
                $childProduct = $productCollection->getItemById($productId);
                $prices[] = (float) $childProduct->getFinalPrice($bundleOption['selection_qty']) * $bundleOption['selection_qty'];

                $bundleOptionsData[$productId]['selection_price_value'] = $childProduct->getFinalPrice($bundleOption['selection_qty']);
                $name[] = $bundleOption['selection_qty'] > 1
                    ? sprintf('%s × %s', $bundleOption['selection_qty'], $childProduct->getName())
                    : $childProduct->getName();

                $sku[] = $bundleOption['selection_qty'] > 1
                    ? sprintf('%sx%s', $bundleOption['selection_qty'], $childProduct->getSku())
                    : $childProduct->getSku();
            }

            if ($product->getNameAutogenerate()) {
                $product->setName(implode(' + ', $name));
            }
            if ($product->getSkuAutogenerate()) {
                $product->setSku(implode('+', $sku));
            } else {
                $productInfo = $this->getRequest()->getParam('product');
                if (isset($productInfo['bundle_product_sku_type']) && $productInfo['bundle_product_sku_type']) {
                    $product->setSkuType(1);
                }
            }

            $productInfo = $this->getRequest()->getParam('product');
            if (isset($productInfo['bundle_product_weight_type']) && $productInfo['bundle_product_weight_type']) {
                $product->setWeightType(1);
            }


            $product->setPriceType(Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC);
            $product->unsPrice();

            $product->validate();
            Mage::register('product', $product);
            $product->save();
            $result['product_id'] = $product->getId();
            $this->_getSession()->addSuccess(Mage::helper('catalog')->__('The product has been created.'));
            $this->_initLayoutMessages('adminhtml/session');
            $result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = array(
                'message' =>  $e->getMessage(),
                'fields'  => array(
                    'sku'  =>  $product->getSku()
                )
            );

        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = array(
                'message'   =>  $this->__('An error occurred while saving the product. ') . $e->getMessage()
             );
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }
}
