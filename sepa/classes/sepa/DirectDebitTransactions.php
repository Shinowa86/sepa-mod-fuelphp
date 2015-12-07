<?php

namespace Sepa {

	/**
	 * Class SepaDirectDebitTransactions
	 * @package SEPA
	 */
	class Sepa_Direct extends Sepa_Payment {
		/**
		 * Unique identification as assigned by an instructing party for an instructed party to unambiguously identify
		 * the instruction.
		 * @var string
		 */
		private $InstructionIdentification = '';

		/**
		 *Unique identification assigned by the initiating party to unumbiguously identify the transaction.
		 * This identification is passed on, unchanged, throughout the entire end-to-end chain.
		 * @var string
		 */
		private $EndToEndIdentification = '';

		/**
		 * Direct Debit Currency
		 *
		 * @var string
		 */
		const CURRENCY = 'EUR';

		private $currency = '';

		/**
		 * Amount of money to be moved between the debtor and creditor, before deduction of charges, expressed in
		 * the currency as ordered by the initiating party.
		 * @var float
		 */
		private $InstructedAmount = 0.00;

		/**
		 * Unique identification, as assigned by the creditor, to unambiguously identify the mandate. SDD
		 * max 35 length
		 * @var string
		 */
		private $MandateIdentification = '';

		/**
		 * Direct Debit Transaction DateTime
		 * @var string
		 */
		private $DateOfSignature = '';

		/**
		 * Debit Bank BIC
		 * @var string
		 */
		private $BIC = '';

		/**
		 * Debit Name
		 * @var string
		 */
		private $DebtorName = '';

		/**
		 * Direct Debit IBAN
		 * @var string
		 */
		private $IBAN = '';

		/**
		 * Information supplied to enable the matching/reconciliation of an entry with the items that the payment is
		 * intended to settle, such as commercial invoices in an accounts' receivable system, in an unstructured form.
		 * max 140 length
		 * @var string
		 */
		private $directDebitInvoice = '';

		/**
		 * @return string
		 */
		public function getInstructionIdentification() {
			return $this->InstructionIdentification;
		}

		/**
		 * @return string
		 */
		public function getEndToEndIdentification() {
			return $this->EndToEndIdentification;
		}

		/**
		 * @return string
		 */
		public function getMandateIdentification() {
			return $this->MandateIdentification;
		}

		/**
		 * @return string
		 */
		public function getDateOfSignature() {
			return $this->DateOfSignature;
		}

		/**
		 * @return string
		 */
		public function getBIC() {
			return $this->BIC;
		}

		/**
		 * @return string
		 */
		public function getIBAN() {
			return $this->IBAN;
		}

		/**
		 * @return string
		 */
		public function getDebtorName() {
			return $this->DebtorName;
		}

		/**
		 * @return string
		 */
		public function getDirectDebitInvoice() {
			return $this->directDebitInvoice;
		}

		/**
		 * @param $instructionIdentifier
		 */
		public function setInstructionIdentification($instructionIdentifier) {
			$this->InstructionIdentification = $instructionIdentifier;
		}

		/**
		 * @param $instructionIdentifierEndToEnd
		 */
		public function setEndToEndIdentification($instructionIdentifierEndToEnd) {
			$this->EndToEndIdentification = $instructionIdentifierEndToEnd;
		}

		/**
		 * Amount of money to be moved between the debtor and creditor, before deduction of charges, expressed in
		 * the currency as ordered by the initiating party.
		 * @param $amount
		 */
		public function setInstructedAmount($amount) {
			$this->InstructedAmount = $this->amountToString($amount);
		}

		public function setCurrency($currency) {
			$this->currency = strtoupper($currency);
		}

		public function getCurrency() {
			if ( empty($this->currency) || is_null($this->currency) ) {

				$this->currency = self::CURRENCY;
			}
			return $this->currency;
		}

		/**
		 * Unique identification, as assigned by the creditor, to unambiguously identify the mandate.
		 * @param $directDebitSDD
		 */
		public function setMandateIdentification($directDebitSDD) {
			$this->MandateIdentification = $directDebitSDD;
		}

		/**
		 * @param $directDebitDateTime
		 */
		public function setDateOfSignature($directDebitDateTime) {
			$this->DateOfSignature = $directDebitDateTime;
		}

		/**
		 * Financial institution servicing an account for the debtor.
		 * Bank Identifier Code.
		 * max length
		 * @param $BIC
		 * @throws \Exception
		 */
		public function setDebitBIC($BIC) {

			$BIC  = $this->removeSpaces($BIC);

			if ( !$this->checkBIC($BIC)) {

				throw new \Exception(ERROR_MSG_DD_CHECK_BIC . $this->getInstructionIdentification());
			}
			$this->BIC = $BIC;
		}

		/**
		 * Name by which a party is known and which is usually used to identify that party.
		 * @param $name
		 * @throws \Exception
		 */
		public function setDebtorName($name) {
			if ( !$this->checkStringLength($name, 140) ) {

				throw new \Exception(ERROR_MSG_DD_NAME . $this->getInstructionIdentification());
			}
			$this->DebtorName = $name;
		}

		/**
		 * Direct Debit IBAN
		 * max  34 length
		 * @param $IBAN
		 * @throws \Exception
		 */
		public function setDebitIBAN($IBAN) {
			$IBAN = $this->removeSpaces($IBAN);

			if ( !$this->checkIBAN($IBAN) ) {

				throw new \Exception(ERROR_MSG_DD_IBAN . $this->getInstructionIdentification());
			}
			$this->IBAN = $IBAN;
		}

		/**
		 * Direct Debit Invoice
		 * @param $invoice
		 * @throws \Exception
		 */
		public function setDirectDebitInvoice($invoice) {
			if ( !$this->checkStringLength($invoice, 140) ) {

				throw new \Exception(ERROR_MSG_DD_INVOICE_NUMBER . $this->getInstructionIdentification());
			}
			$this->directDebitInvoice = $invoice;
		}

		/**
		 * Amount of money to be moved between the debtor and creditor, before deduction of charges, expressed in the
		 * currency as ordered by the initiating party.
		 * @return float
		 */
		public function getInstructedAmount() {
			return $this->InstructedAmount;
		}

		/**
		 * @return bool
		 */
		public function checkIsValidTransaction() {
			if ( !$this->getIBAN() || !$this->getBIC() || !$this->getDirectDebitInvoice() || !$this->getDebtorName()) {

				return false;
			}
			return true;
		}

		/**
		 * @return \SimpleXMLElement
		 */
		public function getSimpleXMLElementTransaction() {

			//Direct Debit Transaction data
			$directDebitTransactionInformation = new \SimpleXMLElement('<DrctDbtTxInf></DrctDbtTxInf>');
			$paymentIdentification = $directDebitTransactionInformation->addChild('PmtId');
			$paymentIdentification->addChild('InstrId', $this->getInstructionIdentification());
			$paymentIdentification->addChild('EndToEndId', $this->getEndToEndIdentification());

			$directDebitTransactionInformation->addChild('InstdAmt', $this->getInstructedAmount())
				->addAttribute('Ccy', $this->getCurrency());

			$directDebitTransaction = $directDebitTransactionInformation->addChild('DrctDbtTx');
			$mandateRelatedInformation = $directDebitTransaction->addChild('MndtRltdInf');
			$mandateRelatedInformation->addChild('MndtId', $this->getMandateIdentification());
			$mandateRelatedInformation->addChild('DtOfSgntr', $this->getDateOfSignature());

			$debtorAgent  = $directDebitTransactionInformation->addChild('DbtrAgt')
				->addChild('FinInstnId');
			$debtorAgent->addChild('BIC', $this->getBIC());

			$debtor = $directDebitTransactionInformation->addChild('Dbtr');
			$debtor->addChild('Nm', $this->getDebtorName());

			$directDebitTransactionInformation->addChild('DbtrAcct')
				->addChild('Id')
				->addChild('IBAN', $this->getIBAN());
			$directDebitTransactionInformation->addChild('RmtInf')
				->addChild('Ustrd', $this->getDirectDebitInvoice());

			return $directDebitTransactionInformation;
		}
	}
}
