<?php

/**
 * Description of ExportController
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
class ExportController extends Default_Controller_SaveBase {

    protected $result = array();

    protected $emails = array();


    public function preDispatch(){
        parent::preDispatch();
        $this->view->setName('base.htm');

        ini_set('memory_limit','1024M');
        ini_set('max_execution_time',1200);
    }
    
    
    public function postDispatch(){
        $this->view->assign('result', $this->result);
        parent::postDispatch();
    }

    /**
     * create 2 csv with single- and multiused rabattcodes of groupon-rabatt
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.10.2010
     */
    public function grouponAction(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select rcg.code, rcg.sameCustomer,o.id from rabatt_codes_groupon rcg join rabatt_codes rc on rc.code = rcg.code join orders o on o.rabattCodeId = rc.id where o.state > 0 ");
        $result = $db->fetchAll($sql);

        $arraySingleUse = array();
        $arrayMultiUse = array();

        foreach($result as $key => $data){
            if($data['sameCustomer']=='0'){

                $arraySingleUse['0'][] = $data['code'].';'.$data['id'];

            }else{
                // 1
                if(array_key_exists($data['sameCustomer'], $arraySingleUse)){

                    if(!array_key_exists($data['sameCustomer'], $arrayMultiUse)){
                        $arrayMultiUse[$data['sameCustomer']][] = $data['code'].';'.$data['id'];
                    }else{
                        $arrayMultiUse[$data['sameCustomer']][] = $data['code'].';'.$data['id'];
                    }

                }else{
                    $arraySingleUse[$data['sameCustomer']][] = $data['code'].';'.$data['id'];
                }
            }
        }

        #var_dump(count($result));
        #var_dump(count($arraySingleUse));
        #var_dump(count($arrayMultiUse));

        $fpSingleUsed = fopen(APPLICATION_PATH . '/../storage/import/groupon/singleUsed-28-09-2010-clean.csv','r');

        $baseSingleUsed = array();
        while ( ($data = fgetcsv ($fpSingleUsed, 1000, ";")) !== FALSE ) {
            $baseSingleUsed[$data[0]] = $data[0];
        }

        $csvSingle = new Default_Exporter_Csv();
        $csvSingle->addCol('CodeSingleUsed');

        $singleUsed = 0;
        $result = array();
        foreach($arraySingleUse as $k => $v){

            foreach($v as $val){
                $value = explode(';', $val);
                if( !array_key_exists($value[0], $baseSingleUsed) ){
                    $csvSingle->addRow(
                        array(
                            'CodeSingleUsed' => $value[0]
                            )
                            );
                    $result[] = $value[0];
                    $singleUsed++;
                }
            }
        }


        $result[] = 'seit letztem export neu singleUsed: '.$singleUsed;
        $fileSingleUsed = $csvSingle->save();

        #echo 'now sleep for 10 seconds';
        sleep(10);

        $fpMultiUsed = fopen(APPLICATION_PATH . '/../storage/import/groupon/multiUsed-28-09-2010-clean.csv','r');

        $baseMultiUsed = array();
        while ( ($data = fgetcsv ($fpMultiUsed, 1000, ";")) !== FALSE ) {
            $baseMultiUsed[$data[0]] = $data[0];
        }

        $csvMulti = new Default_Exporter_Csv();
        $csvMulti->addCol('CodeMultiUsed');

        $multiUsed = 0;
        foreach($arrayMultiUse as $k => $v){

            foreach($v as $val){
                $value = explode(';', $val);
                if( !array_key_exists($value[0], $baseMultiUsed) ){
                    $csvMulti->addRow(
                        array(
                            'CodeMultiUsed' => $value[0]
                            )
                            );
                    $result[] = $value[0];
                    $multiUsed++;
                }
            }
        }
        $result[] = 'seit letztem export neu multiUsed: '.$multiUsed;
        $fileMultiUsed = $csvMulti->save();
    }
    
}
