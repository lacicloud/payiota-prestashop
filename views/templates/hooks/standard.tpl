{*
** @author PrestaShop SA <contact@prestashop.com>
** @copyright  2007-2013 PrestaShop SA
** @version  Release: $Revision: 1.1 $
**
** International Registered Trademark & Property of PrestaShop SA
**
** Description: CoinPayments.net payment form template
**
** This template is displayed on the payment page and called by the Payment hook
**
** Step 1: The customer is validating this form by clicking on the CoinPayments payment button
** Step 3: The transaction success or failure is sent to you by CoinPayments at the following URL: http://www.mystore.com/modules/coinpayments/controllers/front/validation.php?pps=1
** This step is also called IPN ("Instant Payment Notification")
** Step 4: The customer is redirected to his/her "Order history" page ("My account" section)
*}


<p class="payment_module">
<form name="payiotaform" action="https://payiota.me/external.php" method="GET">
		<input type="hidden" name="address" value="{$address}">
		<input type="hidden" name="price" value="{$price}">
		<input type="hidden" name="success_url" value="{$payiota_success_url}">
		<input type="hidden" name="cancel_url" value="{$payiota_cancel_url}">
		<input type="image" width="301" style="cursor: pointer;" src="https://payiota.me/resources/paynow.png" alt="Click here to complete checkout at PayIOTA.me!">
		</form>
	
</p>