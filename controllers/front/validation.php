<?php
/*
 *  @author PayIOTA.me (Based on CoinPayments.net plugin)
 *  @copyright  Laszlo Molnarfi
 *  @version 1.0
 *  
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @version  Release: $Revision: 1.2.0 $
 *
 *  International Registered Trademark & Property of PrestaShop SA
 */

class PayIOTAValidationModuleFrontController extends ModuleFrontController
{
	/**
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		$this->payiota = new PayIOTA();
				
		if ($this->payiota->active)
		{
			parent::initContent();

			if (Tools::getValue('pps'))
				$this->_paymentStandard();
			}
	}
	

	/**
	 * We will first double-check the order details and then create the order in the database
	 */
	private function _paymentStandard()
	{
		
		if (isset($_POST["verification"]) and $_POST['verification'] == Configuration::get('PAYIOTA_VERIFICATION_KEY')) {
			$errors = array();
			$custom = explode(':', Tools::getValue('custom'));
			if (count($custom) != 2) {
				$errors[] = $this->payiota->l('Invalid value for the "custom" field!');
			}
			else
			{
				$cart = new Cart((int)$custom[0]);		
				if (!Validate::isLoadedObject($cart)) {
					$errors[] = $this->payiota->l('Invalid Cart ID!');
				}
				else
				{
							//use 2 for payment accepted on the dashboard/client order history
							$order_status = 2;
					
						    $customer = new Customer((int)$cart->id_customer);
						    $message =
						    'Transaction ID: '.Tools::getValue('address').'
						    Payment Type: PayIOTA.me
						    Amount paid in USD: '.Tools::getValue('price').'
						    Amount paid in IOTA: '.Tools::getValue('paid_iota').'
						    Time: '.Tools::getValue('created');

						    if ($this->payiota->validateOrder((int)$cart->id, (int)$order_status, $cart->getOrderTotal(true), $this->payiota->displayName, $message, array(), null, false, $customer->secure_key, new Shop((int)$custom[1])))
						    {
						    	/* Store transaction ID and details */
						    	$this->payiota->addTransactionId((int)$this->payiota->currentOrder, Tools::getValue('address'));
						    	$this->payiota->addTransaction('payment', array('id_shop' => (int)$custom[1], 'id_customer' => (int)$this->context->cart->id_customer, 'id_cart' => (int)$this->context->cart->id,
						    	'id_order' => (int)$this->payiota->currentOrder, 'id_transaction' => Tools::getValue('address'), 'amount' => $cart->getOrderTotal(true),
						    	'currency' => $cart->id_currency));

						    }
						    

							die('IPN Accepted');
					
				}
			}
			d($errors);
		}
		else {	
			die('Invalid Verification Key.');
		}
	}
}