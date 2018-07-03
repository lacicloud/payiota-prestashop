{*
** @author PrestaShop SA <contact@prestashop.com>
** @copyright  2007-2013 PrestaShop SA
** @version  Release: $Revision: 1.1 $
**
** International Registered Trademark & Property of PrestaShop SA
**
** Description: PayIOTA.me payment form template
*}


<p class="payment_module">
<form name="payiotaform" action="{$url_to_redirect}" method="POST">
		<input type="hidden" class="hidden" name="step" value="3">
		<input type="image" width="301" style="cursor: pointer;" src="https://payiota.me/resources/paynow.png" alt="Click here to complete checkout at PayIOTA.me!">
</form>	
</p>