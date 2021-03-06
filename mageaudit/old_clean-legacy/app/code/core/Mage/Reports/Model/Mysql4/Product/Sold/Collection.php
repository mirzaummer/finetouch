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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Report Sold Products collection
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Mysql4_Product_Sold_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Mysql4_Product_Sold_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addOrderedQty($from, $to)
            ->setOrder('ordered_qty', 'desc');

        return $this;
    }

    /**
     * Set Store filter to collection
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Mysql4_Product_Sold_Collection
     */
    public function setStoreIds($storeIds)
    {
        if (!empty($storeIds[0])) {
            $storeIds = array_values($storeIds);
            $this->getSelect()->where('order_items.store_id IN(?)', $storeIds);
        }

        return $this;
    }
}
