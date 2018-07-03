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
if (!defined('_PS_VERSION_'))
	exit;

class PayIOTA extends PaymentModule
{

	private $_error = array();
	private $_validation = array();

	public function __construct()
	{
		$this->name = 'payiota';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';

		parent::__construct();

		$this->displayName = "PayIOTA.me - IOTA Payments";
		$this->description = "Accept IOTA Payments with PayIOTA.me!";
		$this->confirmUninstall = $this->l('Are you sure you want to delete PayIOTA.me?');
	}

	/**
	 * PayPal USA installation process:
	 *
	 * Step 1 - Requirements checks (Shop country is USA, Canada or Mexico, cURL extension available)
	 * Step 2 - Pre-set Configuration option values
	 * Step 3 - Install the Addon and create a database table to store transaction details
	 *
	 * @return boolean Installation result
	 */
	public function install()
	{
		/* Configuration of the Payment options */
		Configuration::updateValue('PAYIOTA_API_KEY', '');
		Configuration::updateValue('PAYIOTA_VERIFICATION_KEY', '');

		return parent::install() && $this->registerHook('payment') && $this->registerHook('adminOrder') &&
				$this->registerHook('header') && $this->registerHook('orderConfirmation') && $this->registerHook('shoppingCartExtra') &&
				$this->registerHook('productFooter') && $this->registerHook('BackOfficeHeader') && $this->_installDb();
	}

	/**
	 * Database table installation (to store the transaction details)
	 *
	 * @return boolean Database table installation result
	 */
	private function _installDb()
	{
		return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payiota_transaction` (
			`id_payiota_transaction` int(11) NOT NULL AUTO_INCREMENT,
			`id_shop` int(11) unsigned NOT NULL DEFAULT \'0\',
			`id_customer` int(11) unsigned NOT NULL,
			`id_cart` int(11) unsigned NOT NULL,
			`id_order` int(11) unsigned NOT NULL,
			`id_transaction` varchar(128) NOT NULL,
			`amount1` decimal(56,8) NOT NULL,
			`currency1` varchar(3) NOT NULL,
			`date_add` datetime NOT NULL,
		PRIMARY KEY (`id_payiota_transaction`), KEY `idx_transaction` (`id_order`))
		ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
	}

	/**
	 * Uninstallation process:
	 *
	 * Step 1 - Remove Configuration option values from database
	 * Step 2 - Remove the database containing the transaction details (optional, must be done manually)
	 * Step 3 - Uninstallation of the Addon itself
	 *
	 * @return boolean Uninstallation result
	 */
	public function uninstall()
	{
		$keys_to_uninstall = array('PAYIOTA_API_KEY', 'PAYIOTA_VERIFICATION_KEY');

		$result = true;
		foreach ($keys_to_uninstall as $key_to_uninstall)
			$result &= Configuration::deleteByName($key_to_uninstall);

		/* Uncomment this line if you would like to also delete the Transaction details table */
		/* $result &= Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'payiota_transaction`'); */

		return $result && parent::uninstall();
	}

	/* Configuration section
	 *
	 * @return HTML page (template) to configure the Addon
	 */
	public function getContent()
	{
		/* Loading CSS and JS files */
		//$this->context->controller->addCSS(array($this->_path.'views/css/paypal-usa.css', $this->_path.'views/css/colorpicker.css'));
		//$this->context->controller->addJS(array(_PS_JS_DIR_.'jquery/jquery-ui-1.8.10.custom.min.js', $this->_path.'views/js/colorpicker.js', $this->_path.'views/js/jquery.lightbox_me.js', $this->_path.'views/js/payiota.js'));

		/* Update the Configuration option values depending on which form has been submitted */
		if (Tools::isSubmit('SubmitBasicSettings'))
		{
			$this->_saveSettingsBasic();
			unset($this->_validation[count($this->_validation)-1]);
		}

		if (Configuration::get('PAYIOTA_API_KEY') == '' || Configuration::get('PAYIOTA_VERIFICATION_KEY') == '')
			$this->_error[] = $this->l('In order to use PayIOTA.me, please provide your PayIOTA.me API key and PayIOTA.me verification key.');

		$this->context->smarty->assign(array(
			'payiota_configuration' => Configuration::getMultiple(array('PAYIOTA_API_KEY', 'PAYIOTA_VERIFICATION_KEY')),
		));

		return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
	}

	/*
	 * Configuration section
	 */
	private function _saveSettingsBasic()
	{
		if (!isset($_POST['payiota_api_key']) || !$_POST['payiota_api_key'])
			$this->_error[] = $this->l('Your API Key is required.');
		if (!isset($_POST['payiota_verification_key']) || !$_POST['payiota_verification_key'])
			$this->_error[] = $this->l('Your verification key is required.');

		Configuration::updateValue('PAYIOTA_API_KEY', pSQL(Tools::getValue('payiota_api_key')));
		Configuration::updateValue('PAYIOTA_VERIFICATION_KEY', pSQL(Tools::getValue('payiota_verification_key')));
		

		if (!count($this->_error))
			$this->_validation[] = $this->l('Configuration was updated successfully.');
	}

	/* Payment hook
	 *
	 * @param $params Array Default PrestaShop parameters sent to the hookPayment() method (Order details, etc.)
	 *
	 * @return HTML content (Template) displaying the PayIOTA.me payment method
	 */
	public function hookPayment($params)
	{
		$html = '';
		if ($_GET["payiota"] == "true")
		{

			//sicne we cannot use PHP in .tpl files (not recommended), we will do all our actions here. 
			$custom = $this->context->cart->id.":".$this->context->cart->id_shop;
			

			$postdata = http_build_query(
				 array(
					"action" => "new",
					'api_key' => Configuration::get('PAYIOTA_API_KEY'),
					'custom' => $custom,
					'price' => $this->context->cart->getOrderTotal(true),
					'currency' => $this->context->currency->iso_code,
					'ipn_url' => $this->context->link->getModuleLink('payiota', 'validation', array('pps' => 1))
				)
				);

					$opts = array('http' =>
				    array(
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => $postdata
				    )
				);
					$context  = stream_context_create($opts);
					$response = file_get_contents('https://payiota.me/api.php', false, $context);
					
					//cURL fallback
					if (!$response) {
						
						if(is_callable('curl_init') == false){
							echo "ERROR: file_get_contents failed and cURL is not installed";
							die(1);
						}
						$curl = curl_init();
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl,CURLOPT_POST, 1);
						curl_setopt($curl,CURLOPT_POSTFIELDS, $postdata);
						curl_setopt($curl, CURLOPT_URL, 'https://payiota.me/api.php');
						$response = curl_exec($curl);
						
						if (!$response) {
							echo "ERROR: file_get_contents and cURL failed";
							die(1);
						}
					}
					$response = json_decode($response, true);

					header("Location: https://payiota.me/external.php?address=".$response[0]."&price=".$response[1]."&success_url=".$this->context->link->getPageLink('order-confirmation.php')."&cancel_url=".$this->context->link->getPageLink('order.php'));
					die(0);
		} else {
			$url_to_redirect = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&payiota=true";
			$this->context->smarty->assign('url_to_redirect', $url_to_redirect);
			$html .= $this->display(__FILE__, 'views/templates/hooks/standard.tpl');
		}
		return $html;
	}

	/* Order Transaction ID update
	 * Attach a PayIOTA.me Transaction ID to an existing order (it will be displayed in the Order details section of the Back-office)
	 *
	 * @param $id_order integer Order ID
	 * @param $id_transaction string Transaction ID
	 */
	public function addTransactionId($id_order, $id_transaction)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$new_order = new Order((int)$id_order);
			if (Validate::isLoadedObject($new_order))
			{
				$payment = $new_order->getOrderPaymentCollection();
				if (isset($payment[0]))
				{
					$payment[0]->transaction_id = pSQL($id_transaction);
					$payment[0]->save();
				}
			}
		}
	}

	/* Transaction details update
	 * Attach transactions details to an existing order (it will be displayed in the Order details section of the Back-office)
	 *
	 * @param $type Can be either 'payment' or 'refund' depending on the desired operation
	 * @param $details Array Transaction details
	 *
	 * @return boolean Operation result
	 */
	public function addTransaction($details)
	{
		return Db::getInstance()->Execute('
		INSERT INTO '._DB_PREFIX_.'payiota_transaction (id_shop, id_customer, id_cart, id_order,
		id_transaction, amount1, currency1, date_add)
		VALUES ('.(int)$details['id_shop'].', '.(int)$details['id_customer'].', '.(int)$details['id_cart'].', '.(int)$details['id_order'].',
		\''.pSQL($details['id_transaction']).'\', \''.(float)$details['amount'].'\', \''.pSQL($details['currency']).'\', NOW())');
	}

}