<?php

class UploadController extends Default_Controller_Base {

    public function restaurantAction(){

        $basedir = opendir( APPLICATION_PATH . '/../storage/csv/csvbases/' );
        $baseFiles = array();
        while($file = readdir($basedir) ){
            if ( $file != "." && $file != ".." && $file != ".svn" ){
                $baseFiles[] = $file;
            }
        }

        $uploaddir = opendir( APPLICATION_PATH . '/../storage/csv/csvupload/' );
        $uploadedFiles = array();
        while($file = readdir($uploaddir) ){
            if ( $file != "." && $file != ".." && $file != ".svn" ){
                $uploadedFiles[] = $file;
            }
        }
        sort($uploadedFiles);
        sort($baseFiles);

        
        if($this->getRequest()->isPost()){

            $post = $this->getRequest()->getPost();
            
            $form = new Yourdelivery_Form_Upload();
            
            if($form->isValid($post)){
                if($form->file->isUploaded()){
                    $filename = substr($form->file->getFileName(), strripos($form->file->getFileName(), '/')+1);

                    if(!in_array($filename, $baseFiles)){
                        $this->error(__('Dateiname ungültig.'));
                        $this->_redirect('/upload/restaurant');
                    }
                    
                    if(in_array($filename, $uploadedFiles)){
                        $this->error(__('Datei bereits hochgeladen.'));
                        $this->_redirect('/upload/restaurant');
                    }
                    
                    try{
                        $adapter = new Zend_File_Transfer_Adapter_Http();
                        $adapter->setDestination( APPLICATION_PATH . '/../storage/csv/csvupload/' );
                    }catch(Yourdelivery_Exception_NoFileToOpen $e){
                        //
                    }
                    if (!$adapter->receive()) {
                        $this->error($adapter->getMessages());
                        $this->_redirect('/upload/restaurant');
                    }
                    $this->success(__('Erfolgreich hochgeladen.'));
                    $this->_redirect('/upload/restaurant');
                }
            }else{
                #$this->view->p = $post;
                $this->error($form->getMessages());
            }
        }
         

        
        $this->view->assign('basefiles',$baseFiles);

        $this->view->assign('uploadedfiles', $uploadedFiles);

    }


    public function controlAction(){

        $basedir = opendir( APPLICATION_PATH . '/../storage/csv/csvupload/' );
        $baseFiles = array();
        while($file = readdir($basedir) ){
            if ( $file != "." && $file != ".." && $file != ".svn" ){
                $baseFiles[] = $file;
            }
        }

        $uploaddir = opendir( APPLICATION_PATH . '/../storage/csv/csvcontrol/' );
        $uploadedFiles = array();
        while($file = readdir($uploaddir) ){
            if ( $file != "." && $file != ".." && $file != ".svn" ){
                $uploadedFiles[] = $file;
            }
        }
        sort($uploadedFiles);
        sort($baseFiles);


        if($this->getRequest()->isPost()){

            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Upload();

            if($form->isValid($post)){
                if($form->file->isUploaded()){
                    $filename = substr($form->file->getFileName(), strripos($form->file->getFileName(), '/')+1);

                    if(!in_array($filename, $baseFiles)){
                        $this->error(__('Dateiname ungültig.'));
                        $this->_redirect('/upload/control');
                    }

                    if(in_array($filename, $uploadedFiles)){
                        $this->error(__('Datei bereits hochgeladen.'));
                        $this->_redirect('/upload/control');
                    }

                    try{
                        $adapter = new Zend_File_Transfer_Adapter_Http();
                        $adapter->setDestination( APPLICATION_PATH . '/../storage/csv/csvcontrol/' );
                    }catch(Yourdelivery_Exception_NoFileToOpen $e){
                        //
                    }
                    if (!$adapter->receive()) {
                        $this->error($adapter->getMessages());
                        $this->_redirect('/upload/control');
                    }
                    $this->success(__('Erfolgreich hochgeladen.'));
                    $this->_redirect('/upload/control');
                }
            }else{
                #$this->view->p = $post;
                $this->error($form->getMessages());
            }
        }



        $this->view->assign('basefiles',$baseFiles);

        $this->view->assign('uploadedfiles', $uploadedFiles);

    }

}
