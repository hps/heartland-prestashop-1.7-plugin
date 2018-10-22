<?php

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/securesubmitpayment.php');

// if (!defined('_PS_VERSION_'))
// 	exit;

$securesubmit = new SecureSubmitPayment();
if ($securesubmit->active && Tools::getValue('securesubmitToken'))
	$securesubmit->processPayment(Tools::getValue('securesubmitToken'));
else
	die('Token required, please check for any Javascript error on the payment page.');