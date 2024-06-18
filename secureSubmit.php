<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SecureSubmit extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'secureSubmit';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.0';
        $this->author = 'Heartland';
        $this->need_instance = 0;
        $this->bootstrap = true;

        // $this->controllers = array('payment', 'validation');


        parent::__construct();

        $this->displayName = $this->l('Secure Submit');
        $this->description = $this->l('Pay by credit card through Heartland using Secure Submit');

        $this->confirmUninstall = $this->l('Warning: Are you sure you want to uninstall the Secure Submit payment module?');

        $this->limited_countries = array('US');

        $this->limited_currencies = array('USD');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentOptions') &&
            Configuration::updateValue('hps_mode', 0) &&
            Configuration::updateValue('hps_public_key_test', null) &&
            Configuration::updateValue('hps_secret_key_test', null) &&
            Configuration::updateValue('hps_public_key_live', null) &&
            Configuration::updateValue('hps_secret_key_live', null) &&
            Configuration::updateValue('hps_enable_fraud', 0) &&
            Configuration::updateValue('hps_fraud_message', 'Please contact us to complete the transaction.') &&
            Configuration::updateValue('hps_fraud_velocity_attempts', 3) &&
            Configuration::updateValue('hps_fraud_velocity_timeout', 10);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
        Configuration::deleteByName('hps_mode') &&
        Configuration::deleteByName('hps_public_key_test') &&
        Configuration::deleteByName('hps_secret_key_test') &&
        Configuration::deleteByName('hps_public_key_live') &&
        Configuration::deleteByName('hps_secret_key_live') &&
        Configuration::deleteByName('hps_enable_fraud') &&
        Configuration::deleteByName('hps_fraud_message') &&
        Configuration::deleteByName('hps_fraud_velocity_attempts') &&
        Configuration::deleteByName('hps_fraud_velocity_timeout');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSecureSubmitModule')) == true) {
            $this->postProcess();
        }

        $requirements = $this->checkRequirements();
        $requirements_output = '';

        $requirements_output .=
            '<div class="securesubmit-module-wrapper">
                '.(Tools::isSubmit('SubmitSecureSubmit') ? '<div class="conf confirmation">'.$this->l('Settings successfully saved').'<img src="http://www.prestashop.com/modules/'.$this->name.'.png?api_user='.urlencode($_SERVER['HTTP_HOST']).'" style="display: none;" /></div>' : '').'
                <section class="technical-checks">
                    <div class="panel-heading"><i class="icon-warning"></i>&nbsp;Technical Checks</div>
                    <div class="'.($requirements['result'] ? 'conf">'.$this->l('Good news! Everything looks to be in order. Start accepting credit card payments now.') :
                    'warn">'.$this->l('Unfortunately, at least one issue is preventing you from using SecureSubmit. Please fix the issue and reload this page.')).'</div><br/>
                    <table cellspacing="0" cellpadding="0" class="securesubmit-technical">';
        foreach ($requirements as $k => $requirement) {
            if ($k != 'result') {
                $requirements_output .= '
                            <tr>
                                <td style="vertical-align:top;"><span class="heartland icon icon-'.($requirement['result'] ? 'check' : 'remove').'"></span></td>
                                <td>'.$requirement['name'].(!$requirement['result'] && isset($requirement['resolution']) ? '<br />'.Tools::safeOutput($requirement['resolution'], true) : '').'</td>
                            </tr>';
            }
        }
        $requirements_output .= '
                    </table>
                </section>
            <br />';

        $this->context->smarty->assign('requirements', $requirements_output);
        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSecureSubmitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => '',
                        'name' => '',
                        'label' => '<h2>API Key Mode</h2>',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Process Live Credit Cards?'),
                        'name' => 'hps_mode',
                        'is_bool' => true,
                        'desc' => $this->l('Select YES to start processing real credit cards. Select NO for sandbox/testing.'),
                        'values' => array(
                            array(
                                // 'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Live')
                            ),
                            array(
                                // 'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Test')
                            )
                        ),
                    ),
                    array(
                        'type' => '',
                        'name' => '',
                        'label' => '<h2>Sandbox/Testing</h2>',
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'hps_public_key_test',
                        'label' => $this->l('Public Key'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'hps_secret_key_test',
                        'label' => $this->l('Secret Key'),
                    ),
                    array(
                        'type' => '',
                        'name' => '',
                        'label' => '<h2>Production</h2>',
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'hps_public_key_live',
                        'label' => $this->l('Public Key'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'hps_secret_key_live',
                        'label' => $this->l('Secret Key'),
                    ),
                    array(
                        'type' => '',
                        'name' => '',
                        'label' => '<h2>Fraud Protection</h2>',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Fraud Protection'),
                        'name' => 'hps_enable_fraud',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                // 'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                // 'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'name' => 'hps_fraud_message',
                        'label' => $this->l('Display Message'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'hps_fraud_velocity_attempts',
                        'label' => $this->l('How many failed attempts before blocking?'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'hps_fraud_velocity_timeout',
                        'label' => $this->l('How long (in minutes) should we keep a tally of recent failures?'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'hps_mode' => Configuration::get('hps_mode'),
            'hps_public_key_test' => Configuration::get('hps_public_key_test'),
            'hps_secret_key_test' => Configuration::get('hps_secret_key_test'),
            'hps_public_key_live' => Configuration::get('hps_public_key_live'),
            'hps_secret_key_live' => Configuration::get('hps_secret_key_live'),
            'hps_enable_fraud' => Configuration::get('hps_enable_fraud'),
            'hps_fraud_message' => Configuration::get('hps_fraud_message'),
            'hps_fraud_velocity_attempts' => Configuration::get('hps_fraud_velocity_attempts'),
            'hps_fraud_velocity_timeout' => Configuration::get('hps_fraud_velocity_timeout'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Load Javascripts and CSS related to the SecureSubmit's module
     * during the checkout process only.
     *
     * @return string SecureSubmit's JS dependencies
     */
    public function hookHeader()
    {
        if (!in_array($this->context->currency->iso_code, $this->limited_currencies)) {
            return;
        }

        if (Tools::getValue('controller') != 'order-opc' && (!($_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order.php' || $_SERVER['PHP_SELF'] == __PS_BASE_URI__.'order-opc.php' || Tools::getValue('controller') == 'order' || Tools::getValue('controller') == 'orderopc' || Tools::getValue('step') == 3))) {
            return;
        }

        $this->context->controller->addJS('https://js.globalpay.com/v1/globalpayments.js');
        $this->context->controller->addCSS($this->_path.'views/css/securesubmit.css');
        $this->context->controller->addJS($this->_path.'views/js/secure.submit-1.0.2.js');
        $this->context->controller->addJS($this->_path.'views/js/securesubmit.js');

        return '<script type="text/javascript">var securesubmit_public_key = \''.addslashes(Configuration::get('hps_mode') ? Configuration::get('hps_public_key_live') : Configuration::get('hps_public_key_test')).'\';</script>';
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        
        if (Tools::getValue('error') != null) {
            $this->context->smarty->assign('error', Tools::getValue('error'));
        }

        if (Tools::getValue('maxAttemptsExceeded') != null) {
            $this->context->smarty->assign('failure', Configuration::get('hps_fraud_message'));
        }

        /* If the currency is not supported, then leave */
        if (!in_array($this->context->currency->iso_code, $this->limited_currencies)) {
            return ;
        }

        $newOption = new PaymentOption();
        $newOption
                ->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay by Credit Card (Secure Submit)', array()))
                ->setAdditionalInformation($this->display(__FILE__, 'views/templates/hook/payment.tpl'));

        return [$newOption];
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['order'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->context->smarty->assign('status', 'ok');
        }
    }

    /**
     * Check settings requirements to make sure the SecureSubmit's
     * API keys are set.
     *
     * @return boolean Whether the API Keys are set or not.
     */
    public function checkSettings()
    {
        if (Configuration::get('hps_mode')) {
            return Configuration::get('hps_public_key_live') != '' && Configuration::get('hps_secret_key_live') != '';
        } else {
            return Configuration::get('hps_public_key_test') != '' && Configuration::get('hps_secret_key_test') != '';
        }
    }
    
    /**
     * Check technical requirements to make sure the SecureSubmit's module will work properly
     *
     * @return array Requirements tests results
     */
    public function checkRequirements()
    {
        $tests = array('result' => true);

        if (Configuration::get('hps_mode')) {
            $tests['ssl'] = array('name' => $this->l('SecureSubmit requires SSL to be enabled for production.'), 'result' => Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'));
        }
        
        $tests['currencies'] = array('name' => $this->l('The currency USD must be enabled on your store'), 'result' => Currency::exists('USD', 0));
        $tests['configuration'] = array('name' => $this->l('You must set your SecureSubmit Public and Secret API Keys'), 'result' => $this->checkSettings());

        foreach ($tests as $k => $test) {
            if ($k != 'result' && !$test['result']) {
                $tests['result'] = false;
            }
        }

        return $tests;
    }
}
