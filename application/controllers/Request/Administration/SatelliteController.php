<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SatelliteController
 *
 * @author matthiaslaug
 */
class Request_Administration_SatelliteController extends Default_Controller_RequestAdministrationBase {

    public function templateAction() {
        $request = $this->getRequest();
        $this->view->setDir('request/administration/satellite/templates');
        switch ($request->getParam('s')) {
            default :
                die();
            case 'elements':
                $this->view->setName('elements.htm');
                break;
            case 'index':
                $this->view->setName('index.htm');
                break;
        }
        
        
    }

    public function saveAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        $template = $request->getParam('template');
        $templatename= $request->getParam('templatename');
        $id = (integer) $request->getParam('id');

        $storage = new Default_File_Storage();
        $storage->setSubFolder('satellites');
        $storage->setSubFolder($id);
        $storage->setSubFolder('template');     

        $storage->store($templatename.'.html', $template);
    }

    /**
     * 
     *
     * @author Tmeuschke
     */
    public function backgroundimageAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $id = (integer) $request->getParam('id');
        $object = $request->getParam('object');
        $templatename= $request->getParam('templatename');
        $storage = new Default_File_Storage();
        $storage->setSubFolder('satellites');
        $storage->setSubFolder($id);
        $storage->setSubFolder('backgroundimages');
        $storage->setSubFolder($templatename);
        $uploaddir = $storage->getCurrentFolder();
        $file = $uploaddir . '/' . $object . '.png';

        /**
         *@todo use ZEND Adapter here 
         */
        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
            echo "success";
        } else {
            echo "error";
        }
    }

    public function imageAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $id = (integer) $request->getParam('id');
        $object = $request->getParam('object');
        $templatename= $request->getParam('templatename');
        $storage = new Default_File_Storage();
        $storage->setStorage(APPLICATION_PATH . '/../storage');
        $storage->setSubFolder('satellites');
        $storage->setSubFolder($id . '/');
        $storage->setSubFolder('images');
        $storage->setSubFolder($templatename);
        $uploaddir = $storage->getCurrentFolder();
        $file = $uploaddir . '/' . $object . '.png';

        /**
         *@todo use ZEND Adapter here 
         */
        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
            echo "success";
        } else {
            echo "error";
        }
    }

}

