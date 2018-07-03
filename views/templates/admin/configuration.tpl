{*
** @author PrestaShop SA <contact@prestashop.com>
** @copyright  2007-2013 PrestaShop SA
** @version  Release: $Revision: 1.2.0 $
**
** International Registered Trademark & Property of PrestaShop SA
**
** Description: PayIOTA's configuration page
**
*}
<div>
	{if $payiota_validation}
		<div class="conf">
			{foreach from=$payiota_validation item=validation}
				{$validation|escape:'htmlall':'UTF-8'}<br />
			{/foreach}
		</div>
	{/if}
	{if $coinpayments_error}
		<div class="error">
			{foreach from=$payiota_error item=error}
				{$error|escape:'htmlall':'UTF-8'}<br />
			{/foreach}
		</div>
	{/if}
	<form action="{$coinpayments_form_link|escape:'htmlall':'UTF-8'}" method="post" id="payiota_settings" class="half-form L">
		<fieldset>
			<legend><img src="{$module_dir}img/settings.png" alt="" /><span>{l s='PayIOTA Settings' mod='payiota'}</span></legend>
			<div id="paypal-usa-basic-settings-table">
				<label for="payiota_api_key">{l s='PayIOTA API Key:' mod='payiota'}</label></td>
				<div class="margin-form">
					<input type="text" size=48 name="payiota_api_key" class="input-text" value="{if $payiota_configuration.PAYIOTA_API_KEY}{$payiota_configuration.PAYIOTA_API_KEY|escape:'htmlall':'UTF-8'}{/if}" /> <sup>*</sup>
				</div>
				<label for="payiota_verification_key">{l s='PayIOTA Verification Key:' mod='payiota'}</label></td>
				<div class="margin-form">
					<input type="text" size=32 name="payiota_verification_key" class="input-text" value="{if $payiota_configuration.PAYIOTA_VERIFICATION_KEY}{$payiota_configuration.PAYIOTA_VERIFICATION_KEY|escape:'htmlall':'UTF-8'}{/if}" /> <sup>*</sup>
				</div>
			</div>
			<div class="margin-form">
				<input type="submit" name="SubmitBasicSettings" class="button" value="{l s='Save settings' mod='payiota'}" />
			</div>
			<span class="small"><sup style="color: red;">*</sup> {l s='Required fields' mod='payiota'}</span>
		</fieldset>
	</form>
</div>
