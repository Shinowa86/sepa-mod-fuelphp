<?php

namespace Sepa {
	/**
	 * Class SepaGroupHeader
	 * @package SEPA
	 */
	class Sepa_Header extends Sepa_Message {

		/**
		 * Point to point reference assigned by the instructing party and sent to the next party in the chain
		 * to unambiguously identify the message.
		 * Max35Text
		 * @var string
		 */
		private $messageIdentification = '';

		/**
		 * Date and time at which a (group of) payment instruction(s) was created by the instructing party.
		 * @var
		 */
		private $CreationDateTime = '';

		/**
		 * Unique and unambiguous way of identifying an organisation
		 * @var string
		 */
		private $OrganisationIdentification = '';

		/**
		 * Unique and unambiguous identification of a person, eg, passport.
		 * @var string
		 */
		private $PrivateIdentification = '';

		/**
		 * Name by which a party is known and which is usually used to identify that party.
		 * max length 140
		 * @var string
		 */
		private $InitiatingPartyName = '';

		/**
		 * Total of all individual amounts included in the message, irrespective of currencies
		 * @var float
		 */
		private $ControlSum = 0.00;

		/**
		 * Total number of transactions
		 * max length 15
		 * @var int
		 */
		private $NumberOfTransactions = 0;

		/**
		 * Group header Mesage Id setter
		 * var length max 35;
		 * @param $msgId
		 * @throws \Exception
		 */
		public function setMessageIdentification($msgId) {
			if ( !$this->checkStringLength($msgId, 35) ) {
				throw new \Exception(ERROR_MSG_MESSAGE_IDENTIFICATION);
			}
			$this->messageIdentification = $msgId;
		}

		/**
		 * @return string
		 */
		public function getMessageIdentification() {

			return $this->messageIdentification;
		}



		/**
		 * Setter for the time of creation of the group header (hence of the sepa message)
		 * @param $CreDtTm
		 */
		public function setCreationDateTime($CreDtTm) {

			$this->CreationDateTime = $CreDtTm;
		}

		public function getCreationDateTime() {
			$date = new \DateTime();
			if( !$this->CreationDateTime ) {

				$this->CreationDateTime = str_replace(' ', 'T', $date->format('Y-m-d h:i:s'));
			}
			return $this->CreationDateTime;
		}

		/**
		 * Unique and unambiguous way of identifying an organisation
		 * @param $organisationId
		 */
		public function setOrganisationIdentification($organisationId) {
			$this->OrganisationIdentification = $organisationId;
		}

		/**
		 * Setter for the sepa creditor identifier
		 * @param $PrvtId
		 */
		public function setPrivateIdentification($PrvtId) {
			$this->PrivateIdentification = $PrvtId;
		}

		/**
		 * Party that initiates the payment. This can either be the creditor or a party that initiates the
		 * direct debit on behalf of the creditor.
		 * @param $name
		 * @throws \Exception
		 */
		public function setInitiatingPartyName($name) {
			$name = $this->unicodeDecode($name);

			if ( !$this->checkStringLength($name, 140)) {

				throw new \Exception(ERROR_MSG_INITIATING_PARTY_NAME);
			}
			$this->InitiatingPartyName = $name;
		}

		public function getInitiatingPartyName() {

			return $this->InitiatingPartyName;
		}


		/**
		 * This method returns the total Amount that has been registered for all payment info
		 * @param $amount
		 * @return float
		 */
		public function setControlSum($amount) {
			$this->ControlSum += $amount;
		}


		/**
		 * Get total number of transactions
		 * @param $nbTransactions
		 * @return int
		 */
		public function setNumberOfTransactions($nbTransactions) {

			$this->NumberOfTransactions += $nbTransactions;
		}


		/**
		 * This method returns the total Amount that has been registered for all payment info
		 * @return float
		 */
		public function getControlSum() {

			return $this->amountToString($this->ControlSum);
		}


		/**
		 * Get total number of transactions
		 * @return int
		 */
		public function getNumberOfTransactions() {

			return $this->NumberOfTransactions;
		}


		/**
		 * Returns a XML for the group Header object
		 * @return \SimpleXMLElement
		 */
		public function getSimpleXmlGroupHeader() {

			$id = null;
			$groupHeader = new \SimpleXMLElement("<GrpHdr></GrpHdr>");
			$groupHeader->addChild('MsgId', $this->getMessageIdentification());
			$groupHeader->addChild('CreDtTm', $this->getCreationDateTime());
			$groupHeader->addChild('NbOfTxs', $this->getNumberOfTransactions());
			$groupHeader->addChild('CtrlSum', $this->getControlSum());

			$initiatingParty = $groupHeader->addChild('InitgPty');
			$initiatingParty->addChild('Nm', $this->getInitiatingPartyName());

			if (!empty($this->OrganisationIdentification) || !empty($this->PrivateIdentification)) {
				$id = $initiatingParty->addChild('Id');
			}

			if ( !empty($this->OrganisationIdentification) ) {

				$id->addChild('OrgId', $this->OrganisationIdentification);

			}

			if ( !empty($this->PrivateIdentification) ) {

				$id->addChild('PrvtId', $this->PrivateIdentification);
			}

			return $groupHeader;
		}
	}
}
