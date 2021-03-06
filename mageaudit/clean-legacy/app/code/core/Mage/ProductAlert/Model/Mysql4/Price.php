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
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Product alert for changed price resource model
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ProductAlert_Model_Mysql4_Price extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('productalert/price', 'alert_price_id');
    }

    /**
     * Before save process, check exists the same alert
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_ProductAlert_Model_Mysql4_Price
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_null($object->getId()) && $object->getCustomerId() && $object->getProductId() && $object->getWebsiteId()) {
            if ($row = $this->_getAlertRow($object)) {
                $price = $object->getPrice();
                $object->addData($row);
                if ($price) {
                    $object->setPrice($price);
                }
                $object->setStatus(0);
            }
        }
        if (is_null($object->getAddDate())) {
            $object->setAddDate(Mage::getModel('core/date')->gmtDate());
        }
        return parent::_beforeSave($object);
    }

    /**
     * Retrieve alert row by object parameters
     *
     * @param Mage_Core_Model_Abstract $object
     * @return array|bool
     */
    protected function _getAlertRow(Mage_Core_Model_Abstract $object)
    {
        if ($object->getCustomerId() && $object->getProductId() && $object->getWebsiteId()) {
            $sql = $this->_getWriteAdapter()->select()
                ->from($this->getMainTable())
                ->where('customer_id=?', $object->getCustomerId())
                ->where('product_id=?', $object->getProductId())
                ->where('website_id=?', $object->getWebsiteId());
            return $this->_getWriteAdapter()->fetchRow($sql);
        }
        return false;
    }

    /**
     * Load object data by parameters
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_ProductAlert_Model_Mysql4_Price
     */
    public function loadByParam(Mage_Core_Model_Abstract $object)
    {
        $row = $this->_getAlertRow($object);
        if ($row) {
            $object->setData($row);
        }
        return $this;
    }

    /**
     * Delete all customer alerts on website
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $customerId
     * @param int $websiteId
     * @return Mage_ProductAlert_Model_Mysql4_Price
     */
    public function deleteCustomer(Mage_Core_Model_Abstract $object, $customerId, $websiteId = null)
    {
        $where   = array();
        $where[] = $this->_getWriteAdapter()->quoteInto('customer_id=?', $customerId);
        if ($websiteId) {
            $where[] = $this->_getWriteAdapter()->quoteInto('website_id=?', $websiteId);
        }
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        return $this;
    }
}
