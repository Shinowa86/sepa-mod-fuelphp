<?php

namespace Sepa;
	require_once 'error_messages.php';
	require_once '/unicode_decode/code/Unidecode.php';
    require_once '/iban/iban_validation.func.php';


	/**
	 * Class XMLGenerator
	 * @package SEPA
	 */
	class Sepa_Xmlgenerator extends Sepa_Validation {

		const INITIAL_HEADLINE = '<?xml version="1.0" encoding="UTF-8"?>
							<Document
								xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02"
								xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
								xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd">
							</Document>';

		private $sepaMessageObjects = array();

		private $xml;

        /**
         * @var	array	contains references if multiple were loaded
         * THIS MUST BE DEFINED IN THE BASE EXTENSION
         */
        // protected static $_instances = array();

        public static function forge(array $config = array())
        {
            throw new \AuthException('Driver must have a factory method extension.');
        }

		public function __construct() {
			$this->xml = new \SimpleXMLElement(self::INITIAL_HEADLINE);
		}

		public function addXmlMessage(Sepa_Message $messageObject) {

			$this->sepaMessageObjects[] = $messageObject;
		}

        private static function delTree($dir) {
            if(is_dir($dir)){
                $files = array_diff(scandir($dir), array('.','..'));
                foreach ($files as $file) {
                    (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
                }
                return rmdir($dir);
            }else{
                return true;
            }
        }

        public function saveXML($id,$fileName = null) {
            if ( $fileName ) {

                $dom = new \DOMDocument('1.0');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($this->xml->asXML());

                return ($dom->save($fileName) ? true : false);
            }

            /** @var $message Message */
            foreach ($this->sepaMessageObjects as $message ) {
                $this->simpleXmlAppend($this->xml, $message->getSimpleXMLElementMessage());
            }

            $directorio = DOCROOT . 'salidas/sepa/'.$id;


            $this->delTree($directorio);

            mkdir($directorio,0777);
            chmod($directorio,0777);

            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($this->xml->asXML());
            $dom->save(DOCROOT . 'salidas/sepa/'.$id.'/sepa.xml');


            return $this->xml->asXML();
        }

		protected function simpleXmlAppend($to, $from) {
			$toDom = dom_import_simplexml($to);
			$fromDom = dom_import_simplexml($from);

			$toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
		}

    }