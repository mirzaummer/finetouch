<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Adminhtml catalog product action attribute update controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Controller_Action
{

    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Mage_Catalog');
    }

    public function editAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        /* Collect Data */
        $inventoryData      = $this->getRequest()->getParam('inventory', array());
        $attributesData     = $this->getRequest()->getParam('attributes', array());
        $websiteRemoveData  = $this->getRequest()->getParam('remove_website_ids', array());
        $websiteAddData     = $this->getRequest()->getParam('add_website_ids', array());

        /* Prepare inventory data item options (use config settings) */
        foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            if ($attributesData) {
                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $storeId    = $this->_getHelper()->getSelectedStoreId();

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = Mage::getSingleton('eav/config')
                        ->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(array(
                                'date_format' => $dateFormat
                            ));
                            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
                            ));
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    } else if ($attribute->getFrontendInput() == 'multiselect') {
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                }

                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($this->_getHelper()->getProductIds(), $attributesData, $storeId);
            }
            if ($inventoryData) {
                $stockItem = Mage::getModel('cataloginventory/stock_item');

                foreach ($this->_getHelper()->getProductIds() as $productId) {
                    $stockItem->setData(array());
                    $stockItem->loadByProduct($productId)
                        ->setProductId($productId);

                    $stockDataChanged = false;
                    foreach ($inventoryData as $k => $v) {
                        $stockItem->setDataUsingMethod($k, $v);
                        if ($stockItem->dataHasChangedFor($k)) {
                            $stockDataChanged = true;
                        }
                    }
                    if ($stockDataChanged) {
                        $stockItem->save();
                    }
                }
            }

            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel Mage_Catalog_Model_Product_Action */
                $actionModel = Mage::getSingleton('catalog/product_action');
                $productIds  = $this->_getHelper()->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                /**
                 * @deprecated since 1.3.2.2
                 */
                Mage::dispatchEvent('catalog_product_to_website_change', array(
                    'products' => $productIds
                ));

                $this->_getSession()->addNotice(
                    $this->__('Please refresh "Catalog URL Rewrites" and "Product Attributes" in System -> <a href="%s">Index Management</a>', $this->getUrl('adminhtml/process/list'))
                );
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were updated',
                count($this->_getHelper()->getProductIds()))
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating the product(s) attributes.'));
        }

        $this->_redirect('*/catalog_product/', array('store'=>$this->_getHelper()->getSelectedStoreId()));
    }

    /**
     * Validate selection of products for massupdate
     *
     * @return boolean
     */
    protected function _validateProducts()
    {
        if (!is_array($this->_getHelper()->getProductIds())) {
            $this->_getSession()->addError($this->__('Please select products for attributes update'));
            $this->_redirect('*/catalog_product/', array('_current'=>true));
            return false;
        }

        return true;
    }

    /**
     * Rertive data manipulation helper
     *
     * @return Mage_Adminhtml_Helper_Catalog_Product_Edit_Action_Attribute
     */
    protected function _getHelper()
    {
        return Mage::helper('adminhtml/catalog_product_edit_action_attribute');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/update_attributes');
    }
}
