<?php

class SecureSubmitValidationModuleFrontController extends ModuleFrontController
{
    const ONE_MINUTE_IN_SECONDS = 60;
    
    public function postProcess()
    {
        /**
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }

        if (Tools::getValue('securesubmitToken') == null) {
            die('Token required, please check for any Javascript error on the payment page.');
        }

        $cart = Context::getContext()->cart;

        $cart_id = $cart->id;
        $amount = $cart->getOrderTotal();
        $customer_id = Context::getContext()->customer->id;

        /**
         * Restore the context from the $cart_id & the $customer_id to process the validation properly.
         */
        Context::getContext()->cart = new Cart((int)$cart_id);
        Context::getContext()->customer = new Customer((int)$customer_id);
        Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);

        $secure_key = Context::getContext()->customer->secure_key;

        $module_name = $this->module->displayName;
        $currency_id = (int)Context::getContext()->currency->id;

        $response = $this->processPayment(Tools::getValue('securesubmitToken'));

        if ($response) {
            $payment_status = Configuration::get('PS_OS_PAYMENT');
            $message = '';
            $this->module->validateOrder($cart_id, $payment_status, $amount, $module_name, $message, array(), $currency_id, false, $secure_key);

            $new_order = new Order((int)$this->module->currentOrder);
            if (Validate::isLoadedObject($new_order)) {
                $payment = $new_order->getOrderPaymentCollection();
                if (isset($payment[0])) {
                    $payment[0]->transaction_id = pSQL($response->transactionId);
                    $payment[0]->save();
                }
            }

            $module_id = $this->module->id;
            $order_id = $this->module->currentOrder;
            Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$secure_key);
        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');
            // Payment failed, go back to order page
            Tools::redirect('index.php?controller=order&error=PaymentFailedToProcess');
        }
    }

    /**
     * Process a payment with SecureSubmit.
     *
     * @param string $token SecureSubmit card token returned by JS call
     */
    public function processPayment($token)
    {
        require_once(_PS_MODULE_DIR_.'secureSubmit/lib/Hps.php');
        
        $address = new Address((int)$this->context->cart->id_address_invoice);
        $address_state =  new State($address->id_state);
        $customer = new Customer($this->context->cart->id_customer);
        $amount = $this->context->cart->getOrderTotal();
        
        $config = new HpsServicesConfig();
        $config->secretApiKey = Configuration::get('hps_mode') ? Configuration::get('hps_secret_key_live') : Configuration::get('hps_secret_key_test');
        $config->versionNumber = '3136';
        $config->developerId = '002914';
        $chargeService = new HpsCreditService($config);
        $hpsaddress = new HpsAddress();
        $hpsaddress->address = $address->address1;
        $hpsaddress->city = $address->city;
        $hpsaddress->state = $address_state->name;
        $hpsaddress->zip = preg_replace('/[^0-9]/', '', $address->postcode);
        $hpsaddress->country = $address->country;
        $cardHolder = new HpsCardHolder();
        $cardHolder->firstName = $customer->firstname;
        $cardHolder->lastName = $customer->lastname;
        $cardHolder->phone = preg_replace('/[^0-9]/', '', $address->phone);
        $cardHolder->emailAddress = $customer->email;
        $cardHolder->address = $hpsaddress;
        $hpstoken = new HpsTokenData();
        $hpstoken->tokenValue = $token;
        
        $details = new HpsTransactionDetails();
        $details->invoiceNumber = $this->context->cart->id;
        $details->memo = 'PrestaShop Order Number: '.(int)$this->context->cart->id;
        /** Currently saved plugin settings */
        /** This is the message show to the consumer if the rule is flagged */
        $fraud_message              = (string)  $this->getVelocityMsg();
        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts    = (int)     $this->getVelocityLimit();
        /** Maximum amount of time in minutes to track failures. If this amount of time elapse between failures then the counter($HeartlandHPS_FailCount) will reset */
        $fraud_velocity_timeout     = (int)     $this->getVelocityTimeOut();
        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount     = (int)     $this->getVelocityCount();
        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:
         * $fraud_message
         *
         * $fraud_velocity_attempts
         *
         * $fraud_velocity_timeout
         *
         */
        $enable_fraud               = (bool)    $this->getVelocitySetting();
        try {
            /**
             * if fraud_velocity_attempts is less than the $HeartlandHPS_FailCount then we know
             * far too many failures have been tried
             */
            if ($enable_fraud && $HeartlandHPS_FailCount >= $fraud_velocity_attempts) {
                sleep(5);
                $issuerResponse = (string)$this->getVelocityLastResponse();
                $payment_status = Configuration::get('PS_OS_ERROR');
                // Payment failed, go back to order page
                Tools::redirect('index.php?controller=order&maxAttemptsExceeded=true');
            }

            $response = $chargeService->charge(
                $amount,
                "usd",
                $hpstoken,
                $cardHolder,
                false,
                $details
            );
            
            $ResponseMessage = 'Success';
        } catch (HpsException $e) {
            // if advanced fraud is enabled, increment the error count
            if ($enable_fraud) {
                $this->incVelocityCounter();
                if ($this->getVelocityCount() < $fraud_velocity_attempts) {
                    $this->setVelocityLastResponse($e->getMessage());
                }
            }

            $ResponseMessage = 'Failure';
        }
        /* Log Transaction details */
        if (!isset($message) && isset($response)) {
            $message = $this->l('SecureSubmit Transaction Details:')."\n\n".
            $this->l('Transaction ID:').' '.$response->transactionId."\n";
        }

        if ($ResponseMessage == "Failure" || $response->responseCode != '00') {
            return false;
        }

        return $response;
    }

    /** If the Velocity checks are enabled
     * @return bool
     */
    private function getVelocitySetting()
    {
        $this->setVelocityTimer();
        return (bool)$this->get_setting("enable_fraud", '1') ;
    }

    /** Gets the message to be displayed to the end user if the rule is triggered
     * @return string
     */
    private function getVelocityMsg()
    {
        return $this->get_setting("fraud_message", 'Please contact us to complete the transaction.');
    }

    /** Number of minutes between attempts to retain failure counts
     * @return int
     */
    private function getVelocityTimeOut()
    {
        return (int) $this->get_setting("fraud_velocity_timeout", '10');
    }

    /** Number of failures before the rule is applied
     * @return int
     */
    private function getVelocityLimit()
    {
        return (int) $this->get_setting("fraud_velocity_attempts", '3');
    }

    /** Sets the timer to the current time in seconds. Unix time stamp
     * @return mixed
     */
    private function setVelocityTimer()
    {
        $this->context->cookie->LastCheck = time();
        return $this->context->cookie->LastCheck;
    }

    /** Checks if the current settings have timed out
     * @return bool
     */
    private function isVelocityTimedOut()
    {
        return (bool) ($this->context->cookie->LastCheck < (time()-(self::ONE_MINUTE_IN_SECONDS*$this->getVelocityTimeOut())));
    }

    /** Returns the current if any count of Failed transactions
     * @return int
     */
    private function getVelocityCount()
    {
        return (int) $this->context->cookie->VelocityCount;
    }

    /** increment or reset the velocity counter. This counter is limited in duration by \SecureSubmitPayment::isVelocityTimedOut
     * @return int
     */
    private function incVelocityCounter()
    {
        if ($this->getVelocitySetting()) {
            if ($this->isVelocityTimedOut()) {
                $this->context->cookie->VelocityCount = 0;
            } else {
                $this->context->cookie->VelocityCount = $this->getVelocityCount() + 1;
            }
            $this->setVelocityTimer();
        } else {
            $this->context->cookie->VelocityCount = 0;
        }
        return (int) $this->getVelocityCount();
    }

    /** gets any response currently stored
     * @param $IssuerResponse
     */
    private function getVelocityLastResponse()
    {
        $re = '';
        if ($this->getVelocitySetting() && $this->isVelocityTimedOut()) {
            $re = $this->context->cookie->IssuerResponse;
            $this->setVelocityTimer();
        }
        return $re;
    }

    /** Set the last issuer response. stores it so it can be displayed back to the user on subsequent failures
     * @return int
     */
    private function setVelocityLastResponse($IssuerResponse)
    {
        $this->context->cookie->IssuerResponse = '';
        if ($this->getVelocitySetting()) {
            $this->context->cookie->IssuerResponse = trim(filter_var($IssuerResponse, FILTER_SANITIZE_STRING));
        }
        return $this->getVelocityLastResponse();
    }

    /** Uses built in Prestashop API \ToolsCore::safeOutput(\ConfigurationCore::get) to get a setting but accepts a default value to be returned if not set
     * @param string $setting
     * @param string $default
     * @return string string
     */
    private function get_setting($setting, $default)
    {
        $ret = trim(Tools::safeOutput(Configuration::get('hps_' . $setting)));
        if ($ret === '') {
            $ret = $default;
        }
        return $ret;
    }
}
