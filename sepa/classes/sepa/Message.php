<?php

namespace Sepa {
	/**
	 * Class SepaMessage
	 * @package SEPA
	 */
	class Sepa_Message extends Sepa_Xmlgenerator {

		/**
		 * @var$groupHeaderObjects GroupHeader
		 */
		private $groupHeaderObjects;

		/**
		 * @var $message \SimpleXMLElement
		 */
		private $message;

		/**
		 * @var $storeXmlPaymentsInfo \SimpleXMLElement
		 */
		private $storeXmlPaymentsInfo;


		/**
		 * @var array
		 */
		private $paymentInfoObjects = array();

		public function __construct() {
			$this->message = new \SimpleXMLElement('<CstmrDrctDbtInitn></CstmrDrctDbtInitn>');
			$this->storeXmlPaymentsInfo = new \SimpleXMLElement('<payments></payments>');
		}

		/**
		 * Add Group Header
		 * @param GroupHeader $groupHeaderObject
		 */
		public function setMessageGroupHeader(Sepa_Header $groupHeaderObject) {
			if ( is_null($this->groupHeaderObjects) ) {

				$this->groupHeaderObjects = $groupHeaderObject;
			}
		}

		/**
		 * @return GroupHeader
		 */
		public function getMessageGroupHeader() {

			return $this->groupHeaderObjects;
		}

		/**
		 * Add Message Payment Info
		 * @param PaymentInfo $paymentInfoObject
		 */
		public function addMessagePaymentInfo(Sepa_Payment $paymentInfoObject) {
			$paymentInfoObject->resetNumberOfTransactions();
			$paymentInfoObject->resetControlSum();
			$this->paymentInfoObjects[] = $paymentInfoObject;
		}

		/**
		 * Get Simple Xml Element Message
		 * @return \SimpleXMLElement
		 */
		public function getSimpleXMLElementMessage() {

			/**
			 * @var $paymentInfo PaymentInfo
			 */
			foreach ($this->paymentInfoObjects as $paymentInfo) {

				$paymentInfo->resetControlSum();
				$paymentInfo->resetNumberOfTransactions();

				$this->simpleXmlAppend($this->storeXmlPaymentsInfo, $paymentInfo->getSimpleXMLElementPaymentInfo());

				$this->getMessageGroupHeader()->setNumberOfTransactions($paymentInfo->getNumberOfTransactions());
				$this->getMessageGroupHeader()->setControlSum($paymentInfo->getControlSum());
			}

			$this->simpleXmlAppend($this->message, $this->getMessageGroupHeader()->getSimpleXmlGroupHeader());

			foreach (dom_import_simplexml($this->storeXmlPaymentsInfo)->childNodes as $element) {

				$this->simpleXmlAppend($this->message, $element);
			}

			return $this->message;
		}
	}
}
