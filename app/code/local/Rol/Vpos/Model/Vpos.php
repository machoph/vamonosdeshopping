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

class Rol_Vpos_Model_Vpos extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'vpos';
     
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    
    /**
     * Config instance
     * @var Mage_Paypal_Model_Config
     */
    protected $_config = null;
    
    /**
    * Return Order place redirect url
    *
    * @return string
    */
    public function getOrderPlaceRedirectUrl()
    {
        //when you click on place order you will be redirected on this url, if you don't want this action remove this method
        return Mage::getUrl('vpos/vpos/redirect', array('_secure' => true));
    }
    
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Return form field array
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
    	/*
    	  C—digo Adquirente: 99
á         C—digo Comercio testing: 6000
á         C—digo mall: 0
á         C—digo Terminal: 00000000 (8 ceros)
		  crc o 188
á         El url de pruebas es el siguiente: https://test2.alignetsac.com/VPOS/MM/transactionStart20.do
    	 */
       $orderIncrementId =  $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $customer = Mage::getModel('customer/session')->getCustomer();
        $billingaddress = $order->getBillingAddress();
        //print_r($order->getBillingAddress());
        $array_send['acquirerId']=12;
        $array_send['commerceId']=4577;
        
        $array_send['purchaseOperationNumber']= $orderIncrementId;
        $array_send['purchaseAmount']=number_format($order->getGrandTotal(), 2, '', '');
        $array_send['purchaseCurrencyCode']=188;//$order->getBaseCurrencyCode(); //188 ;
       // echo 'work';
       // $array_send['purchaseIPAddress']= $_SERVER['REMOTE_ADDR']; //188 ;
        $array_send['commerceMallId']=0;
        $array_send['terminalCode']='00000000';
        $array_send['billingFirstName']=$billingaddress->getData('firstname');
        $array_send['billingLastName']=$billingaddress->getData('lastname');
        $array_send['billingEMail']=$billingaddress->getData('email');
        
        $array_send['billingAddress']= substr($billingaddress->getData('street'),0, 15);
        $array_send['billingZIP']=$billingaddress->getData('postcode'); 
        $array_send['billingCity']=$billingaddress->getData('city');
        $array_send['billingState']=$billingaddress->getData('region');
        $array_send['billingCountry']=$billingaddress->getData('country_id');
        $array_send['billingPhone']=$billingaddress->getData('telephone');
		$array_send['language']='SP'; //En espanol
        
        $llavePrivada = '';
        $llavePublica = '';
        
        $array_get['XMLREQ']="";
        $array_get['DIGITALSIGN']="";
        $array_get['SESSIONKEY']="";
        
        //VAMONOS.CIFRADO.PUBLICA.pem
        //VAMONOS.FIRMA.PRIVADA.pem
        //VAMONOS.CIFRADO.PRIVADA.pem
        //ALIGNET.TESTING.PHP.CRYPTO.PUBLIC.txt
        //ALIGNET.TESTING.PHP.SIGNATURE.PUBLIC.txt
        
        $VI = "0123456789ABCDEF";
       $llaveFirmaPriv = file_get_contents("/home/vamonosdeshoping/VAMONOS.PROD.FIRMA.PRIVADA.pem");
       $llaveCifradoPriv = file_get_contents("/home/vamonosdeshoping/VAMONOS.PROD.CIFRADO.PRIVADA.pem");
       $llaveVPOSCryptoPub = file_get_contents("/home/vamonosdeshoping/ALIGNET.PRODUCCION.PHP.CRYPTO.PUBLIC.txt");
       $llaveVPOSFirmaPub = file_get_contents("/home/vamonosdeshoping/ALIGNET.PRODUCCION.PHP.SIGNATURE.PUBLIC.txt");
       
       //echo MAGENTO_ROOT . '/includes/vpos.php';
       VPOSSend($array_send,$array_get,$llaveVPOSCryptoPub,$llaveFirmaPriv,$VI);
       
      // echo ($llavePriv);
      //  print_r($array_get);
        
        $result = array(
        	'IDACQUIRER'  => 12,
        	'IDCOMMERCE'  => 4577,
        	'XMLREQ'	  => $array_get['XMLREQ'],
        	'DIGITALSIGN' => $array_get['DIGITALSIGN'],
        	'SESSIONKEY'  => $array_get['SESSIONKEY']					
        	/*	
            'ordernumber'   => 'WEBSHOP' . $orderIncrementId,
            'lang'          => strtoupper(substr(Mage::app()->getStore()->getCode(), 0, 2)),
            'merchantid'    => Mage::getStoreConfig('payment/vpos/merchant_id'),
            'currency'      => 'EUR',
            'name'          => preg_replace('/[^A-Za-z0-9\s\s+]/', '', $customer->getDefaultBillingAddress()->getCompany()),
            'clientemail'   => $customer->getEmail(),
            'amount'        => number_format($order->getGrandTotal(), 2, '.', ''),
            'mailparams'    => 'ordernumber',
            'forwardparams' => ''*/
        );        

        return $result;
    }
}
