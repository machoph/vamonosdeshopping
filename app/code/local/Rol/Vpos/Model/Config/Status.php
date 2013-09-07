<?php
/**
 * Rol_Vpos
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@raiffeisen.net so we can send you a copy immediately.
 *
 *
 * @category    Rol
 * @package     Rol_Vpos
 * @copyright   Copyright (c) 2011 Raiffeisen OnLine Soc. coop. (http://www.raiffeisen.net)
 * @author      Klemens Rauch <klemens.rauch@raiffeisen.it> <klemens.r@gmail.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Rol_Vpos_Model_Config_Status
{
    // set null to enable all possible
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PROCESSING,
        Mage_Sales_Model_Order::STATE_COMPLETE,
    );

    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        $options[] = array(
               'value' => '',
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            );
        foreach ($statuses as $code=>$label) {
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        return $options;
    }
}
