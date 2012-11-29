<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnalyzeController
 *
 * @author abriliano
 */


class Administration_AnalyzeController extends Default_Controller_AdministrationBase {
    /**
     * upload file
     */
    public function startAction(){
    $request = $this->getRequest();

        if($request->isPost()) {

            $form = new Yourdelivery_Form_Administration_Analyze_Start();
            $post = $request->getPost();
            if($form->isValid($post)) {
                // upload new file
                
                $filename = substr_replace(explode("tmp",$form->file->getFileName()),'',0,1);
                $total = count($filename);
                $last = $total-1;
                $newFileName = $filename[$last];
                if(file_exists(APPLICATION_PATH.'/../storage/import/'.$newFileName)) {
                    rename(APPLICATION_PATH.'/../storage/import/'.$newFileName,APPLICATION_PATH.'/../storage/import/'.time().$newFileName);
                }
                
                if($form->file->isUploaded() ) {
                     try{
                        $adapter = new Zend_File_Transfer_Adapter_Http();
                        $adapter->setDestination( APPLICATION_PATH . '/../storage/import/' );
                    }catch(Yourdelivery_Exception_NoFileToOpen $e){}

                if (!$adapter->receive()) {
                        $this->error($adapter->getMessages());
                        $this->_redirect('/administration_analyze/start');
                    }

                    $this->success(__b("Erfolgreich hochgeladen."));
                    $this->session->filename = APPLICATION_PATH.'/../storage/import/'.$newFileName;
                    $this->_redirect('/administration_analyze/show');
                    
                }
            }
            else {
                $this->error($form->getMessages());


            }
        }
    }

    /**
     * read files and display it
     */
    public function showAction(){
        // file einlesen
        $file = $this->session->filename;
        $handle = fopen($file, "r") or die(__b("Cannot open file"));

        $finalLeads = '';

        $i = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if ($i > 0)
                $finalLeads[] = $data;

            $i++;
        }
        fclose($handle);


        // statistiken auslesen
        $anzahlLeads = count($finalLeads);
        $converted = 0;
        $dead = 0;
        $inProcess = 0;
        $group = 0;
        $hot = 0;
        $recycled = 0;
        $neu = 0;
        $moritz = 0;
        $torsten = 0;
        $xu = 0;
        $can = 0;

        $today = date('Y-m-d');
        $today7 = strtotime ( '-7 day' , strtotime ( $today ) ) ;
        $today7 = date ( 'Ymd' , $today7 );

        $today14 = strtotime ( '-14 day' , strtotime ( $today ) ) ;
        $today14 = date ( 'Ymd' , $today14 );

        $today21 = strtotime ( '-21 day' , strtotime ( $today ) ) ;
        $today21 = date ( 'Ymd' , $today21 );


        foreach ($finalLeads AS $array) {



            //welcher status
            if ($array[5] == 'Dead')
                    $dead++;

            if ($array[5] == 'In Process')
                    $inProcess++;

            if ($array[5] == 'Hot') {
                    $hot++;
                    $hotArray[] = $array[6];
            }


            if ($array[5] == 'Recycled')
                    $recycled++;

            if ($array[5] == 'New')
                    $neu++;

            if ($array[5] == 'Converted') {
                    $converted++;
                    $convertedArray[] = $array[6];
            }

            //which user assigned=
            if ($array[8] == 'torsten')
                    $torsten++;

            if ($array[8] == 'xu')
                    $xu++;

            if ($array[8] == 'moritz')
                    $moritz++;

            if ($array[8] == 'can')
                    $can++;

            // wann kontaktaufnahme?
            if (preg_match('/\//', $array[10])) {
                    $datum_array=explode("/",$array[10]);
                    $datum = $datum_array[2] . $datum_array[0] . $datum_array[1];
            } else {
                    $datum_array=explode(".",$array[10]);
                    $datum = $datum_array[2] . $datum_array[1] . $datum_array[0];
            }

            if ($datum > $today7) {$kontakt7['anzahl']++;

                    if ($array[8] == 'torsten')
                         $kontakt7['torsten']++;
                    elseif ($array[8] == 'xu')
                         $kontakt7['xu']++;
                    elseif ($array[8] == 'moritz')
                         $kontakt7['moritz']++;
                    elseif ($array[8] == 'can')
                         $kontakt7['can']++;


            } elseif ($datum > $today14)  {
                    $kontakt14['anzahl']++;

                    if ($array[8] == 'torsten')
                         $kontakt14['torsten']++;
                    elseif ($array[8] == 'xu')
                         $kontakt14['xu']++;
                    elseif ($array[8] == 'moritz')
                         $kontakt14['moritz']++;
                    elseif ($array[8] == 'can')
                         $kontakt14['can']++;

            }
            elseif ($datum > $today21) {
                    $kontakt21['anzahl']++;


                    if ($array[8] == 'torsten')
                         $kontakt21['torsten']++;
                    elseif ($array[8] == 'xu')
                         $kontakt21['xu']++;
                    elseif ($array[8] == 'moritz')
                         $kontakt21['moritz']++;
                    elseif ($array[8] == 'can')
                         $kontakt21['can']++;
            }


            // wann zuletzt modified?
            if (preg_match('/\//', $array[2])) {
                    $datum_array=explode("/",$array[2]);
                    $datum = substr($datum_array[2], 0, 4) . $datum_array[0] . $datum_array[1];
            } else {
                    $datum_array=explode(".",$array[2]);
                    $datum = substr($datum_array[2], 0, 4) . $datum_array[1] . $datum_array[0];
            } if ($datum > $today7) {

                    $modified7['anzahl']++;

                    if ($array[8] == 'torsten')
                         $modified7['torsten']++;
                    elseif ($array[8] == 'xu')
                         $modified7['xu']++;
                    elseif ($array[8] == 'moritz')
                         $modified7['moritz']++;
                    elseif ($array[8] == 'can')
                         $modified7['can']++;


            }
            elseif ($datum > $today14)  {
                    $modified14['anzahl']++;

                    if ($array[8] == 'torsten')
                         $modified14['torsten']++;
                    elseif ($array[8] == 'xu')
                         $modified14['xu']++;
                    elseif ($array[8] == 'moritz')
                         $modified14['moritz']++;
                    elseif ($array[8] == 'can')
                         $modified14['can']++;

            }
            elseif ($datum > $today21) {
                    $modified21['anzahl']++;


                    if ($array[8] == 'torsten')
                         $modified21['torsten']++;
                    elseif ($array[8] == 'xu')
                         $modified21['xu']++;
                    elseif ($array[8] == 'moritz')
                         $modified21['moritz']++;
                    elseif ($array[8] == 'can')
                         $modified21['can']++;
            }
        }
        $convertedPerc['normal'] = round($converted/$anzahlLeads*100,1);
        $convertedPerc['neu'] = round($converted/($anzahlLeads-$neu)*100,1);
        $convertedPerc['neuProc'] = round($converted/($anzahlLeads-$neu-$inProcess)*100,1);
        $hotPerc['normal'] = round($hot/$anzahlLeads*100,1);
        $hotPerc['neu'] = round($hot/($anzahlLeads-$neu)*100,1);
        $hotPerc['neuProc'] = round($hot/($anzahlLeads-$neu-$inProcess)*100,1);
        $deadPerc['normal'] = round($dead/$anzahlLeads*100,1);
        $deadPerc['neu'] = round($dead/($anzahlLeads-$neu)*100,1);
        $deadPerc['neuProc'] = round($dead/($anzahlLeads-$neu-$inProcess)*100,1);
        $recycledPerc['normal'] = round($recycled/$anzahlLeads*100,1);
        $recycledPerc['neu'] = round($recycled/($anzahlLeads-$neu)*100,1);
        $recycledPerc['neuProc'] = round($recycled/($anzahlLeads-$neu-$inProcess)*100,1);
        $inProcessPerc['normal'] = round($inProcess/$anzahlLeads*100,1);
        $inProcessPerc['neu'] = round($inProcess/($anzahlLeads-$neu)*100,1);
        $neuPerc['normal'] = round($neu/$anzahlLeads*100,1);
        $xuPerc['normal'] = round($xu/$anzahlLeads*100,1);
        $canPerc['normal'] = round($can/$anzahlLeads*100,1);
        $torstenPerc['normal'] = round($torsten/$anzahlLeads*100,1);
        $moritzPerc['normal'] = round($moritz/$anzahlLeads*100,1);
        $this->view->assign('anzahlLeads',$anzahlLeads);
        $this->view->assign('neu',$neu);
        $this->view->assign('neuPerc',$neuPerc);
        $this->view->assign('inProcess',$inProcess);
        $this->view->assign('inProcessPerc',$inProcessPerc);
        $this->view->assign('converted',$converted);
        $this->view->assign('convertedPerc',$convertedPerc);
        $this->view->assign('hot',$hot);
        $this->view->assign('hotPerc',$hotPerc);
        $this->view->assign('dead',$dead);
        $this->view->assign('deadPerc',$deadPerc);
        $this->view->assign('recycled', $recycled );
        $this->view->assign('recycledPerc', $recycledPerc );
        $this->view->assign('modified14', $modified14 );
        $this->view->assign('modified21', $modified21 );
        $this->view->assign('modified7', $modified7 );
        $this->view->assign('kontakt7', $kontakt7 );
        $this->view->assign('kontakt14', $kontakt14 );
        $this->view->assign('kontakt21', $kontakt21 );
        $this->view->assign('xu', $xu );
        $this->view->assign('xuPerc', $xuPerc );
        $this->view->assign('torsten', $torsten );
        $this->view->assign('torstenPerc', $torstenPerc );
        $this->view->assign('can', $can );
        $this->view->assign('canPerc', $canPerc );
        $this->view->assign('moritz', $moritz );
        $this->view->assign('moritzPerc', $moritzPerc );
        $this->view->assign('convertedArray', $convertedArray);
        $this->view->assign('hotArray', $hotArray);
        $this->view->assign('finalLeads', $finalLeads);
        
    }
}
?>

