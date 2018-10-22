{*
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<div class="row secureSubmit-header">
		<div class="col-md-12" >
			<img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo.png" id="heartland-payment-logo" />
			<span class="gray-header">Secure Submit</span>
		</div>
	</div>

	<hr />
	
	<div class="secureSubmit-content">
		<div class="row">
			<div class="col-md-6">
				<h4>{l s='Benefits of using Secure Submit' mod='secureSubmit'}</h4>
				<ul class="ul-spaced">
					<li>
						<strong>{l s='Simple' mod='secureSubmit'}:</strong>
						{l s='Once the plugin is installed and enabled, it\'s only a matter of adding the API keys from your Heartland merchant account and you\'re ready to go!' mod='secureSubmit'}
					</li>
					<br/>
					<li>
						<strong>{l s='Secure' mod='secureSubmit'}:</strong>
						{l s='Credit card information is processed on Heartland\'s servers and sensitive information is converted to non-sensitive tokens for processing.' mod='secureSubmit'}
					</li>
					<br/>				
				</ul>

			</div>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">
				<p class="text-muted">{l s='All major credit cards accepted' mod='secureSubmit'}</p>
				
				<div class="row">
					<div class="col-md-6">
						<img class="accepted-cards" src="{$module_dir|escape:'html':'UTF-8'}/views/img/ss-shield@2x.png" />
					</div>
					<div class="col-md-6">
						<h5>{l s='For support, call 866-802-9753' mod='secureSubmit'} {l s='or' mod='secureSubmit'} <a href="mailto:SecureSubmitCert@e-hps.com">SecureSubmitCert@e-hps.com</a></h5>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="panel">
	<p class="text-muted">
		<i class="icon icon-info-circle"></i> {l s='You will need a set of API keys to use this plugin.  Follow the link below and log in or register to receive your keys instantly.' mod='secureSubmit'}
	</p>
	<p>
		<a href="https://developer.heartlandpaymentsystems.com/Account/KeysandCredentials" target="_blank"><i class="icon icon-key"></i> Get Your API Keys</a>
	</p>
</div>

<div class="panel">
	{$requirements}
</div>