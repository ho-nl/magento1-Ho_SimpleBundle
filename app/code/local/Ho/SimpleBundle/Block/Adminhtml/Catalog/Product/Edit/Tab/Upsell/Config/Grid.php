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
 * Adminhtml super product links grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ho_SimpleBundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Upsell_Config_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Config attribute codes
     *
     * @var null|array
     */
    protected $_configAttributeCodes = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setId('upsell_bundle_link');
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }

            $createdProducts = $this->_getCreatedProducts();

            $existsProducts = $productIds; // Only for "Yes" Filter we will add created products

            if(count($createdProducts)>0) {
                if(!is_array($existsProducts)) {
                    $existsProducts = $createdProducts;
                } else {
                    $existsProducts = array_merge($createdProducts);
                }
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$existsProducts));
            }
            else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getCreatedProducts()
    {
        $products = $this->getRequest()->getPost('new_products', null);
        if (!is_array($products)) {
            $products = array();
        }

        return $products;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
     */
    protected function _prepareCollection()
    {

        $product = $this->_getProduct();
        $collection = $product->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price')
            ->addFieldToFilter('attribute_set_id',$product->getAttributeSetId())
            ->addFieldToFilter('type_id', Mage::helper('bundle')->getAllowedSelectionTypes())
            ->addFilterByRequiredOptions()
            ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            Mage::getModel('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($collection);
        }

//        foreach ($product->getTypeInstance(true)->getUsedProductAttributes($product) as $attribute) {
//            $collection->addAttributeToSelect($attribute->getAttributeCode());
//            $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
//        }

        $this->setCollection($collection);

        if ($this->isReadonly()) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
        }

        parent::_prepareCollection();
        return $this;
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products_upsell_bundle', array());

        return $products;
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        if ($this->hasData('is_readonly')) {
            return $this->getData('is_readonly');
        }
        return $this->_getProduct()->getCompositeReadonly();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));


        $sets = Mage::getModel('eav/entity_attribute_set')->getCollection()
            ->setEntityTypeFilter($this->_getProduct()->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '130px',
            'index' => 'attribute_set_id',
            'type'  => 'options',
            'options' => $sets,
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'      => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price',
        ));

        $this->addColumn('is_saleable', array(
            'header'    => Mage::helper('catalog')->__('Inventory'),
            'renderer'  => 'adminhtml/catalog_product_edit_tab_super_config_grid_renderer_inventory',
            'filter'    => 'adminhtml/catalog_product_edit_tab_super_config_grid_filter_inventory',
            'index'     => 'is_saleable'
        ));

        if (!$this->isReadonly()) {
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'bundle_product[products]',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id',
//                'renderer'  => 'adminhtml/catalog_product_edit_tab_super_config_grid_renderer_checkbox',
            ));
        }
        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog/data')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'width'             => 60,
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'editable'          => true,
            'edit_only'         => !$this->_getProduct()->getId()
        ));

        $this->addColumn('qty', array(
            'header'            => Mage::helper('catalog/data')->__('Qty'),
            'name'              => 'qty',
            'type'              => 'number',
            'width'             => 60,
            'validate_class'    => 'validate-number',
            'index'             => 'qty',
            'editable'          => true,
            'edit_only'         => !$this->_getProduct()->getId()
        ));

        return parent::_prepareColumns();
    }

    public function getEditParamsForAssociated()
    {
        return array(
            'base'      =>  '*/*/edit',
            'params'    =>  array(
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1,
                'product'  => $this->_getProduct()->getId()
            )
        );
    }

    public function getOptions($attribute) {
        $result = array();
        foreach ($attribute->getProductAttribute()->getSource()->getAllOptions() as $option) {
            if($option['value']!='') {
                $result[$option['value']] = $option['label'];
            }
        }

        return $result;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/catalog_upsell_product/bundle', array('_current'=>true));
    }

    /**
     * Retrieve item row configurable attribute data
     *
     * @param Varien_Object $item
     * @return array
     */
    protected function _retrieveRowData(Varien_Object $item)
    {
        $attributeValues = array();
//        foreach ($this->_getConfigAttributeCodes() as $attributeCode) {
//            $data = $item->getData($attributeCode);
//            if ($data) {
//                $attributeValues[$attributeCode] = $data;
//            }
//        }
        return $attributeValues;
    }

    /**
     * Checking the data contains the same value of data after collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();

        $disableMultiSelect = false;
        $ids = array();
        foreach ($this->_collection as $item) {
            $ids[] = $item->getId();
            $needleAttributeValues = $this->_retrieveRowData($item);
            foreach($this->_collection as $item2) {
                // Skip the data if already checked
                if (in_array($item2->getId(), $ids)) {
                   continue;
                }
                $attributeValues = $this->_retrieveRowData($item2);
                $disableMultiSelect = ($needleAttributeValues == $attributeValues);
                if ($disableMultiSelect) {
                   break;
                }
            }
            if ($disableMultiSelect) {
                break;
            }
        }

        // Disable multiselect column
        if ($disableMultiSelect) {
            $selectAll = $this->getColumn('in_products');
            if ($selectAll) {
                $selectAll->setDisabled(true);
            }
        }

        return $this;
    }
}
