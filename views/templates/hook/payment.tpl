<div class="securesubmitFormContainer">
	<h3><img alt="Secure Icon" class="secure-icon" src="{$module_dir}views/img/locked.png" style="background-size:20px 20px;"/>{l s='Pay by Credit Card (Secure Submit)' mod='secureSubmit'}</h3>
	<img alt="Accepted Cards" class="accepted-cards" src="{$module_dir}views/img/ss-shield@2x.png" />
	<form action="{$link->getModuleLink('secureSubmit', 'validation')|escape:'html'}" method="POST" class="securesubmit-payment-form" id="securesubmit-payment-form"{if isset($securesubmit_credit_card)} style="display: none;"{/if}>
		<div id="securesubmit-payment-errors">
			<div class="secure-submit-error-message d-none"></div>
		</div>
		<div id="secure-submit-card" class="form-group">
			<label>{l s='Card Number*' mod='securesubmitpayment'}</label>
			<div id="securesubmitIframeCardNumber" class="ss-frame-container"></div>
		</div>
		<div id="secure-submit-date" class="form-group">
			<label>{l s='Expiration (MM/YYYY*)' mod='securesubmitpayment'}</label>
			<div id="securesubmitIframeCardExpiration" class="ss-frame-container"></div>
		</div>
		<div id="secure-submit-cvv" class="form-group">
			<label>{l s='CVC*' mod='securesubmitpayment'}</label>
			<div id="securesubmitIframeCardCvv" class="ss-frame-container"></div>
		</div>
		<br />
		<div class="form-group required ">
			<div id="submit_button" class=""></div>
		</div>
	</form>
	{if isset($error)}
		<div class="floating-error alert alert-danger shadow">
			<span>{l s='Your payment failed to process.  Check your credit card information and try again.  If the error persists, please contact support for help completing your order.' mod='secureSubmit'}</span>
		</div>
	{/if}

	{if isset($failure)}
		<!-- Modal -->
		<div class="modal fade" id="failureModal" tabindex="-1" role="dialog" aria-labelledby="failureModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="failureModalLabel">Having Trouble With Your Order?</h5>
					</div>
					<div class="modal-body">
						{$failure}
					</div>
					<div class="modal-footer">
						<a href="/contact-us" class="btn btn-primary">Contact Us</a>
					</div>
				</div>
			</div>
		</div>

	{/if}
</div>
