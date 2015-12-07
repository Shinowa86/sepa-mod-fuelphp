<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @Sepaor     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_core_namespace('Sepa');

Autoloader::add_classes(array(
	'Sepa\\Sepa'                         => __DIR__.'/classes/sepa.php',
	'Sepa\\SepaException'                => __DIR__.'/classes/sepa/error_messages.php',

	'Sepa\\Sepa_Direct'                  => __DIR__.'/classes/sepa/DirectDebitTransactions.php',

	'Sepa\\Sepa_Header'                  => __DIR__.'/classes/sepa/GroupHeader.php',

	'Sepa\\Sepa_Message'                 => __DIR__.'/classes/sepa/Message.php',
	'Sepa\\Sepa_Payment'                 => __DIR__.'/classes/sepa/PaymentInfo.php',
	'Sepa\\Sepa_Validation'              => __DIR__.'/classes/sepa/ValidationRules.php',

	'Sepa\\Sepa_Xmlgenerator'            => __DIR__.'/classes/sepa/Xmlgenerator.php',


));
