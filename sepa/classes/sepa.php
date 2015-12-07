<?php

namespace Sepa;

class Sepa {

    public function calculateBic($account) {

        $c = substr($account, 0, 4);

        $iban = substr($account,0,1);

        if($iban=='E')
            $c = substr($account, 5, 4);

        $code = \Model_Bic::query()->where('codigo',$c)->get_one();

        $bic = $code->BIC;

        return $bic;

    }

    public function calculateIban($account) {

        $iban = substr($account,0,1);

        if($iban=='E'){
            $account = substr($account, 5);
        }

        $seq = $account."142800";

        $seq = str_replace(" ","",$seq);

        $rest = bcmod($seq ,97);

        $code = 98 - $rest;

        if(round($code)<10)
            $final = "ES0".round($code)." ".$account;
        else
            $final = "ES".round($code)." ".$account;

        return $final;

    }

    public function export($id,$file) {

        $xmlGenerator = new Sepa_Xmlgenerator();

        foreach ($file as $_message ) {

            $message = new Sepa_Message();
            //set Message Group header Info
            $groupHeader = new Sepa_Header();

            $groupHeader->setMessageIdentification($_message['message_id']);
            $groupHeader->setInitiatingPartyName($_message['group_header']['company_name']);

            //set Message group header
            $message->setMessageGroupHeader($groupHeader);

            //set Message Payment Info

            foreach ($_message['payment_info'] as $SequenceType => $_paymentInfo ) {

                //set payment info
                $paymentInfo = new Sepa_Payment();
                $paymentInfo->setPaymentInformationIdentification($_paymentInfo['id']);
                $paymentInfo->setSequenceType($SequenceType);
                $paymentInfo->setCreditorAccountIBAN($_paymentInfo['creditor_iban']);
                $paymentInfo->setCreditorAccountBIC($_paymentInfo['creditor_bic']);
                $paymentInfo->setCreditorName($_paymentInfo['creditor_name']);
                $paymentInfo->setCreditorSchemeIdentification($_paymentInfo['scheme_identifier']);

                foreach ($_paymentInfo['transactions'] as $_transaction) {

                    //set payment info transactions
                    $transaction = new Sepa_Direct();
                    $transaction->setInstructionIdentification($_transaction['id']);

                    $transaction->setEndToEndIdentification($_transaction['endId']);
                    $transaction->setInstructedAmount($_transaction['amount']);
                    $transaction->setDebtorName($_transaction['company_name']);
                    $transaction->setDebitIBAN($_transaction['iban']);
                    $transaction->setDebitBIC($_transaction['bic']);
                    $transaction->setMandateIdentification($_transaction['umr']);
                    $transaction->setDateOfSignature($_transaction['mandate_sign_date']);
        		    $transaction->setCurrency('EUR');
                    $transaction->setDirectDebitInvoice($_transaction['invoice']);
                    //add Payment Info transactions
                    $paymentInfo->addDirectDebitTransaction($transaction);

                }
                //add Message Payment Info
                $message->addMessagePaymentInfo($paymentInfo);
            }
            //add Message To Xml File
            $xmlGenerator->addXmlMessage($message);
        }
        $xmlGenerator->saveXML($id);

    }
}
