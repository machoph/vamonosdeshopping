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
include_once MAGENTO_ROOT . '/includes/vpos_plugin.php';
class Rol_Vpos_VposController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer chooses Vpos on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setVposQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('vpos/redirect')->_toHtml());
        $session->unsQuoteId();
    }
    
    public function testAction() {
        exit(var_export($this->getRequest()->getParams(),1));
    }
    
    /**
     * when vpos returns
     */
    public function  successAction()
    {
    	//$order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    	//$order->getPayment()->setAdditionalInformation('payment_message','cacaca')->save();
    	//print_r($order->getPayment()->getAdditionalInformation('payment_message'));
    	//exit;
    	//$this->_redirect('sales/order/history');
    	//print_r($this->getRequest()->getParams()); 

    	$arrayOut =   array();
    	$VI = "0123456789ABCDEF";
    	$llaveFirmaPriv = file_get_contents("/home/vamonosdeshoping/VAMONOS.PROD.FIRMA.PRIVADA.pem");
    	$llaveCifradoPriv = file_get_contents("/home/vamonosdeshoping/VAMONOS.PROD.CIFRADO.PRIVADA.pem");
    	$llaveVPOSCryptoPub = file_get_contents("/home/vamonosdeshoping/ALIGNET.PRODUCCION.PHP.CRYPTO.PUBLIC.txt");
    	$llaveVPOSFirmaPub = file_get_contents("/home/vamonosdeshoping/ALIGNET.PRODUCCION.PHP.SIGNATURE.PUBLIC.txt");
    	if(VPOSResponse($this->getRequest()->getParams(),$arrayOut,$llaveVPOSFirmaPub,$llaveCifradoPriv,$VI)){
    		//La salida esta en $arrayOut con todos los parametros decifrados devueltos por el VPOS
// print_r($arrayOut);
//  exit;
    		$session = Mage::getSingleton('checkout/session');
    		$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
    		//$order = Mage::getModel('sales/order')->loadByIncrementId($arrayOut['purchaseOperationNumber']);
    		if ($order->getId()) 
    		{
	    		
	    		$order->getPayment()->setAdditionalInformation('purchaseOperationNumber',$arrayOut['purchaseOperationNumber'])->save();
	    		$order->getPayment()->setAdditionalInformation('purchaseAmount',$arrayOut['purchaseAmount'])->save();
	    		$order->getPayment()->setAdditionalInformation('purchaseCurrencyCode',$arrayOut['purchaseCurrencyCode'])->save();
	    		$order->getPayment()->setAdditionalInformation('authorizationResult',$arrayOut['authorizationResult'])->save();
	    		$order->getPayment()->setAdditionalInformation('authorizationCode',$arrayOut['authorizationCode'])->save();
	    		$order->getPayment()->setAdditionalInformation('errorCode',$arrayOut['errorCode'])->save();
	    		$order->getPayment()->setAdditionalInformation('errorMessage',$arrayOut['errorMessage'])->save();
    			
	    		if($arrayOut['authorizationResult'] != '00')
	    		{
	    			$order->cancel()->save();
	    		}
	    		else
	    		{
	    			$order->setStatus(Mage::getStoreConfig('payment/vpos/order_status'))->save();
	    		}
	    			
	    	}		
    		//$resultadoAutorizacion = $arrayOut['authorizationResult'];
    		// $codigoAutorizacion = $arrayOut['authorizationCode'];
    	}else{
    		//Puede haber un problema de mala configuracion de las llaves, vector de
    		//inicializacion o el VPOS no ha enviado valores correctos }
    		$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
    		$order->getPayment()->setAdditionalInformation('authorizationResult',-1)>save();
    		$order->getPayment()->setAdditionalInformation('authorizationCode',-1)>save();
    		$order->getPayment()->setAdditionalInformation('errorCode',-1)>save();
    		$order->getPayment()->setAdditionalInformation('errorMessage','VPOS ERROR')->save();
    		
    	}
    		$this->_redirect('sales/order/history/ordernumber/'.$session->getLastRealOrderId(), array('_secure'=>true));
    	
       /*
        if(empty($auth) || $auth == 'ko') {
            $session = Mage::getSingleton('checkout/session');
            $session->setQuoteId($session->getVposQuoteId(true));
            if ($session->getLastRealOrderId()) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
                if ($order->getId()) {
                    $order->cancel()->save();
                }
            }
            $this->_redirect('customer/account');    
        } else {
            $session = Mage::getSingleton('checkout/session');
            $session->setQuoteId($session->getVposQuoteId(true));
            if ($session->getLastRealOrderId()) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
                if ($order->getId()) {
                    $order->setStatus(Mage::getStoreConfig('payment/vpos/order_status'))->save();
                }
            }
            $this->_redirect('sales/order/history', array('_secure'=>true));
            
       }*/
       
    }
}
