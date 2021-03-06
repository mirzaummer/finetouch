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
 * @category    Find
 * @package     Find_Feed
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Attribute map edit form container block
 *
 * @category    Find
 * @package     Find_Feed
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Find_Feed_Block_Adminhtml_Edit_Codes_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Import codes list
     *
     * @return array
     */
    protected function _getImportCodeList()
    {
        $attributes = Mage::getConfig()->getNode(Find_Feed_Model_Import::XML_NODE_FIND_FEED_ATTRIBUTES)->children();
        $result     = array();
        foreach ($attributes as $node) {
            $label = trim((string)$node->label);
            if ($label) {
                $result[$label] = $label;
            }
        }

        return $result;
    }

    /**
     * Magento entity type list for eav attributes
     *
     * @return array
     */
    protected function _getCatalogEntityType()
    {
        return Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();
    }


    /**
     * Magento eav attributes list
     *
     * @return array
     */
    protected function _getEavAttributeList()
    {
        $result     = array();
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach ($collection as $model) {
            $result[$model->getAttributeCode()] = $model->getAttributeCode();
        }
        return $result;
    }

    /**
     * Prepare form
     *
     * @return Varien_Object
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'import_item_form',
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('generate_fieldset', array(
            'legend' => Mage::helper('find_feed')->__('Item params')
        ));
        $fieldset->addField('import_code', 'select', array(
            'label'     => Mage::helper('find_feed')->__('Import code'),
            'name'      => 'import_code',
            'required'  => true,
            'options'   => $this->_getImportCodeList()
        ));
        $fieldset->addField('eav_code', 'select', array(
            'label'     => Mage::helper('find_feed')->__('Eav code'),
            'name'      => 'eav_code',
            'required'  => true,
            'options'   => $this->_getEavAttributeList()
        ));

        $source = Mage::getModel('eav/entity_attribute_source_boolean');
        $isImportedOptions = $source->getOptionArray();

        $fieldset->addField('is_imported', 'select', array(
            'label'     => Mage::helper('find_feed')->__('Is imported'),
            'name'      => 'is_imported',
            'value'     => 1,
            'options'   => $isImportedOptions
        ));
        $form->setUseContainer(true);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
