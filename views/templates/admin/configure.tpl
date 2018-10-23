<div class="tab-content">
	<div class="tab-pane active" id="heartland_config_info">
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
	</div>
</div>