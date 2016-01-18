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
 * @package     Mage_Rating
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Rating entity resource
 *
 * @category   Mage
 * @package    Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Rating_Model_Mysql4_Rating_Entity extends Mage_Core_Model_Mysql4_Abstract
{
    function _construct()
    {
        $this->_init('rating/rating_entity', 'entity_id');
    }

    public function getIdByCode($entityCode)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from($this->getTable('rating_entity'), $this->getIdFieldName())
            ->where('entity_code = ?', $entityCode);
        return $read->fetchOne($select);
    }
}