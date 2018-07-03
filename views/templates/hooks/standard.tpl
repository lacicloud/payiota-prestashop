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
<form name="payiotaform" action="https://payiota.me/external.php" method="GET">
		<input type="hidden" name="address" value="{$address}">
		<input type="hidden" name="price" value="{$price}">
		<input type="hidden" name="success_url" value="{$payiota_success_url}">
		<input type="hidden" name="cancel_url" value="{$payiota_cancel_url}">
		<input type="image" id="submitbutton" width="301" style="cursor: pointer;" src="https://payiota.me/resources/paynow.png" alt="Click here to complete checkout at PayIOTA.me!">
		</form>
</p>
<script>
	document.getElementById('submitbutton').click();
</script>