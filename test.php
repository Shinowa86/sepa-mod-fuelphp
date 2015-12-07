class Tasks
{

    public function run()
    {
    		$id = $this->generateXml($accs);    
    }

    private static function generateXml($accs){

        try{

            $sepacfg = \Config::load('sepa');

            $SEPA = new \Sepa();

            $accounts = $accs->_accounts;

            $debtors = array();
            
            //Composing the XML file with the creditor's info

            $file = array(
                array('message_id' => 1,
                    'group_header' => array(
                        'company_name' => $sepacfg['company_name']
                    ),
                    'payment_info' => array(
                        'FRST' => array(
                            'id' => 1,
                            'creditor_iban' => $sepacfg['creditor_iban'],
                            'creditor_bic' =>$sepacfg['creditor_bic'],
                            'creditor_name' => $sepacfg['creditor_name'],
                            'scheme_identifier' => $sepacfg['scheme_identifier'],
                            'transactions' => array()
                        ),
                    )
                )
            );

            $cont = 1;

						//Composing the XML file with the debtors's info

            foreach ($accounts as $c){

                $acc_db = \Model_Contratos::query()->where('id',$c)->get_one();

                $debtors['id']      = $cont;
                $debtors['endId']   = $cont;
                $debtors['company_name'] = $acc_db->name;
                $debtors['amount']  = $acc_db->amount;
                $debtors['umr']     = $acc_db->dni;

                if($acc_db->bic){
                    $debtors['bic']     = $acc_db->bic;
                }else{
                    if($contrato_db->cuenta){
                        $debtors['bic'] = $SEPA->calculateBic($acc_db->account);
                    }
                }

                if($acc_db->iban){
                    $debtors['iban']    = $acc_db->iban;
                }else{
                    if($acc_db->account){
                        $debtors['iban'] = $SEPA->calculateIban($acc_db->account);
                    }
                }

                $debtors['mandate_sign_date'] = $acc_db->fx_alta;
                $debtors['invoice'] = $cont;

                array_push($file[0]['payment_info']['FRST']['transactions'],$debtors);

                $cont++;

            }

            $SEPA->export($acc_db->id,$file);

            return $acc_db->id;

        }
        catch(Exception $e){
            throw $e;
        }


    }



}