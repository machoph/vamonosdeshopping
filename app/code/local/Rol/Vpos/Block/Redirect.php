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
class Rol_Vpos_Block_Redirect extends Mage_Core_Block_Abstract
{
    public function _toHtml()
    {
        $_vpos = Mage::getModel('vpos/vpos');

        $form = new Varien_Data_Form();
        $form->setAction(Mage::getStoreConfig('payment/vpos/submit_url'))
            ->setId('vpos_checkout')
            ->setName('vposform')
            ->setMethod('POST')
            ->setUseContainer(true);
        
        foreach ($_vpos->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to the Alignet Vpos website in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("vpos_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
