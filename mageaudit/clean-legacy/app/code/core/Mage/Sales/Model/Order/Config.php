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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Order configuration model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Config extends Mage_Core_Model_Config_Base
{
    private $_states;

    public function __construct()
    {
        parent::__construct(Mage::getConfig()->getNode('global/sales/order'));
    }

    protected function _getStatus($status)
    {
        return $this->getNode('statuses/'.$status);
    }

    protected function _getState($state)
    {
        return $this->getNode('states/'.$state);
    }

    /**
     * Retrieve default status for state
     *
     * @param   string $state
     * @return  string
     */
    public function getStateDefaultStatus($state)
    {
        $status = false;
        if ($stateNode = $this->_getState($state)) {
            if ($stateNode->statuses) {
                foreach ($stateNode->statuses->children() as $statusNode) {
                    if (!$status) {
                        $status = $statusNode->getName();
                    }
                    $attributes = $statusNode->attributes();
                    // empty($attributes['default']) is for backwards compatibility
                    if (isset($attributes['default']) && (empty($attributes['default']) || $attributes['default'] == '1')) {
                        $status = $statusNode->getName();
                    }
                }
            }
        }
        return $status;
    }

    /**
     * Retrieve status label
     *
     * @param   string $status
     * @return  string
     */
    public function getStatusLabel($status)
    {
        if ($statusNode = $this->_getStatus($status)) {
            $status = (string) $statusNode->label;
            return Mage::helper('sales')->__($status);
        }
        return $status;
    }

    /**
     * Retrieve all statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        $statuses = array();
        foreach ($this->getNode('statuses')->children() as $status) {
            $label = (string) $status->label;
            $statuses[$status->getName()] = Mage::helper('sales')->__($label);
        }
        return $statuses;
    }

    /**
     * Retrieve statuses available for state
     * Get all possible statuses, or for specified state, or specified states array
     * Add labels by default. Return plain array of statuses, if no labels.
     *
     * @param mixed $state
     * @param bool $addLabels
     * @return array
     */
    public function getStateStatuses($state, $addLabels = true)
    {
        $statuses = array();
        if (empty($state) || !is_array($state)) {
            $state = array($state);
        }
        foreach ($state as $_state) {
            if ($stateNode = $this->_getState($_state)) {
                foreach ($stateNode->statuses->children() as $statusNode) {
                    $status = $statusNode->getName();
                    if ($addLabels) {
                        $statuses[$status] = $this->getStatusLabel($status);
                    }
                    else {
                        $statuses[] = $status;
                    }
                }
            }
        }
        return $statuses;
    }

    /**
     * Retrieve states which are visible on front end
     *
     * @return array
     */
    public function getVisibleOnFrontStates()
    {
        $this->_getStates();
        return $this->_states['visible'];
    }

    /**
     * Get order states, visible on frontend
     *
     * @return array
     */
    public function getInvisibleOnFrontStates()
    {
        $this->_getStates();
        return $this->_states['invisible'];
    }

    private function _getStates()
    {
        if (null === $this->_states) {
            $this->_states = array(
                'all'       => array(),
                'visible'   => array(),
                'invisible' => array(),
                'statuses'  => array(),
            );
            foreach ($this->getNode('states')->children() as $state) {
                $name = $state->getName();
                $this->_states['all'][] = $name;
                if ($state->visible_on_front){
                    $this->_states['visible'][] = $name;
                }
                else {
                    $this->_states['invisible'][] = $name;
                }
                foreach ($state->statuses->children() as $status) {
                    $this->_states['statuses'][$name][] = $status->getName();
                }
            }
        }
    }
}
