<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PictureController
 *
 * @author mlaug
 */
class Administration_Service_Category_PictureController extends Default_Controller_AdministrationBase {


    public function preDispatch(){
        $this->view->assign('navservices', 'active');
        parent::preDispatch();

        $restTable = new Yourdelivery_Model_DbTable_Restaurant();
        $this->view->assign('restIds', $restTable->getDistinctNameId());

        $piccatTable = new Yourdelivery_Model_DbTable_Category_Picture();
        $this->view->assign('picCatIds', $piccatTable->getIdsNames());

    }

    public function listAction(){
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('p'=>'category_picture'),
                                            array(
                                                'ID' => 'p.id',
                                                __b('Name') => 'p.name',
                                                __b('Desc') => 'p.description'
                                            ))
                     ->order('p.id ASC');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID',              array('searchType' => '='))
             ->updateColumn('Name', array('title'=>__b('Name')))
             ->updateColumn('Desc', array('title'=>__b('Description')));
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                    '<div>
                        <a href=\"/administration_service_category_picture/edit/id/{{ID}}\" \" >'.__b("Editieren").'</a><br />
                    </div>'
                );
        $grid->addExtraColumns($option);
        
        $this->view->grid = $grid->deploy();
    }

    /**
     * Show all restaurants where at least one meal category has no picture assigned
     * @author alex
     * @since 09.2010 or so
     */
    public function missingcatpicsAction(){
        
        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
            ->from(array('r' => 'restaurants'), array(
                'ID'         => 'r.id',
                __b('Restaurant') => 'r.name',
                __b('Status')     => 'r.isOnline',
                __b('Adresse')    => new Zend_Db_Expr("CONCAT(r.street, ' ', r.hausnr, ', ', r.plz)"),
            ))
            ->joinLeft(array('mc' => 'meal_categories'), 'mc.restaurantId = r.id', array(
                'MCID' => 'mc.id'
            ))
            ->joinLeft(array('cp' => 'category_picture'), 'mc.categoryPictureId = cp.id', array(
                'MCID' => 'mc.id'
            ))
            ->where('mc.id IS NOT NULL')
            ->where('cp.id IS NULL')
            ->where('r.deleted = 0')
            ->order('r.name')
            ->group('r.id');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('MCID',       array('hidden' => 1));
        $grid->updateColumn('ID',              array('searchType' => '='));
        $grid->updateColumn('Restaurant', array('title'=>__b('Restaurant'),'decorator' => '<a href="/administration_service/edit/id/{{ID}}">{{Restaurant}}</a>'));
        $grid->updateColumn('Status',     array('title'=>__b('Status'),'callback' => array('function' => 'statusToReadable', 'params' => array('{{Status}}'))));
        $grid->updateColumn('Adresse', array('title'=>__b('Adresse')));

        $activeStatis = array(
            0 => __b('Offline'),
            1 => __b('Online'),
            ''  => __b('Alle')
        );

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('ID')
            ->addFilter(__b('Restaurant'))
            ->addFilter(__b('Status'), array('values' => $activeStatis));
        $grid->addFilters($filters);

        // add header script
        $this->view->headerScript = $grid->getHeaderScript();
        
        // deploy grid to view
        $this->view->grid = $grid->deploy();
        
    }
    
    public function createAction(){

        $request = $this->getRequest();
        if ( $request->isPost() ){
            $name = $request->getParam('name',null);
            $desc = $request->getParam('description',null);
            if ( is_null($name) || empty($name) ){
                $this->error(__b("Bitte tragen sie einen Namen ein"));
                return;
            }
            $cat = new Yourdelivery_Model_Category_Picture();
            $cat->setName($name);
            $cat->setDescription($desc);
            $id = $cat->save();
            $this->success(__b("Kategorie erfolgreich angelegt"));
            $this->_redirect('/administration_service_category_picture/edit/id/' . $id);
        }
    }

    public function editAction(){

        $id = $this->getRequest()->getParam('id',null);
        if ( !is_null($id) ){
            try{
                $cat = new Yourdelivery_Model_Category_Picture($id);
                $this->view->cat = $cat;
            }
            catch( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->_error(__b("Konnte Bildkategorie nicht finden"));
                $this->_redirect('/administration_service_category_picture/list');
            }
        }
        else{
            $this->_redirect('/administration_service_category_picture/list');
        }

        $request = $this->getRequest();

        if ( $request->isPost() ){

            if(!is_null($request->getParam('updatecat',null))){
                try{
                    $catpic = new Yourdelivery_Model_Category_Picture($id);
                    $name = $request->getParam('name',null);
                    $desc = $request->getParam('description',null);
                    $catpic->setName($name);
                    $catpic->setDescription($desc);
                    $catpic->save();
                    $this->success(__b("Kategorie erfolgreich bearbeitet"));
                }catch( Yourdelivery_Exception_Database_Inconsistency $e ){
                    $this->error(__b("Konnte Kategorie nicht speichern"));
                }
                $this->_redirect('/administration_service_category_picture/edit/id/'.$id);
            }


            if(!is_null($request->getParam('uploadfile',null))){
                $catpic = new Yourdelivery_Model_Category_Picture($id);

                $form = new Yourdelivery_Form_Administration_Category_Picture_Edit();
                $post = $request->getPost();

                if ( $form->isValid($post) ) {
                    $values = $form->getValues();

                    $filename = $form->img->getFileName();

                    //add new image to the category images
                    if($form->img->isUploaded() ) {
                        if($catpic->setImg($filename)){
                            $this->success(__b("Bild wurde erfolgreich hochgeladen"));
                        }else{
                            $this->error(__b("Bild wurde nicht hochgeladen"));
                        }
                        $this->_redirect('/administration_service_category_picture/edit/id/'.$id);
                    }
                }
                else {
                    $this->error($form->getMessages());
                }
            }
        }
    }

    public function assignAction(){
        $request = $this->getRequest();
        $id = $request->getParam('id',null);
        if ( !is_null($id) && $id != '' ){
            try{
                $service = new Yourdelivery_Model_Servicetype_Restaurant($id);
                $this->view->service = $service;
                $this->view->picCat = Yourdelivery_Model_DbTable_Category_Picture::all();
            }
            catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
                $this->_redirect('/administration_service_category_picture/list');
            }
        }
        else{
            $this->warn(__b("Kein Restaurant gewählt"));
            $this->_redirect('/administration_service_category_picture/list');
        }

        // save assignment for each meal_categorie
        if ( $request->isPost() && !is_null($request->getParam('submitassign',null))){
            $pcats = $request->getParam('pcat',null);
            $errors = null;
            foreach($pcats as $catId => $pcat){
                try{
                    $cat = new Yourdelivery_Model_Meal_Category($catId);
                    $cat->setCategoryPictureId($pcat[0]);
                    $img = $cat->getImage(true);
                    $cat->save();

                    // delete image if $pcat[0] == null / == ''
                    if(is_null($pcat[0]) || $pcat[0]=='0'){
                        if ( is_file(APPLICATION_PATH . $img) ){
                            try{
                                unlink(APPLICATION_PATH . $img);
                            }catch(Exception $e){
                                $this->warn(__b("Konnte Datei ").APPLICATION_PATH.$img.__b(" nicht löschen"));
                                continue;
                            }
                        }
                    }

                }catch (Yourdelivery_Exception_Database_Inconsistency $e){
                    continue;
                }
            }

            $this->success(__b("Kategorien erfolgreich bearbeitet"));
            $this->_redirect('/administration_service_edit/piccategories/id/' . $id);
        }


    }



}
?>
