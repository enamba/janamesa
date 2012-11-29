<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Administration_Service_CategoryController extends Default_Controller_AdministrationBase {

    public function  preDispatch() {
        parent::preDispatch();
        $this->view->assign('navservices', 'active');
    }

    /**
     * Grid with restaurants categories
     * @author alex
     * @since 15.12.2010
     */
    public function indexAction(){
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);

        //select categories
        $select = $db->select()->from(array('rc'=>'restaurant_categories'),
                                            array(
                                                __b('ID') => 'rc.id',
                                                __b('Name') => 'rc.name',
                                                __b('Description') => 'rc.description',
                                                __b('Google Kategorie') => 'rc.googleCategoryId'
                                            ))
                     ->order('rc.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Description'))
                ->addFilter(__b('Google Kategorie'));

        $grid->addFilters($filters);
        $option = new Bvb_Grid_Extra_Column();

        $option->position('right')->name('Options')->decorator(
                '<div>
                    <a href="/administration_service_category/edit/id/{{'.__b('ID').'}}">'.__b("Editieren").'</a><br />
                    <a href="/administration_service_category/delete/id/{{'.__b('ID').'}}" onclick="javascript:return confirm(\''.__b("Vorsicht!! Soll diese Kategorie wirklich gel&ouml;scht werden?").'\')">'.__b("L&ouml;schen").'</a>
                </div>'
            );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();

    }

    /**
     * Create new restaurant category
     * @author alex
     * @since 15.12.2010
     */
    public function createAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            $this->_redirect('/administration_service_category');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            //create new category
            $form = new Yourdelivery_Form_Administration_Service_Category_Edit();
            if($form->isValid($post)) {
                $values = $form->getValues();

                $category = new Yourdelivery_Model_Servicetype_Categories();

                $category->setData($values);
                $category->save();

                $this->success(__b("Kategorie wurde erfolgreich erstellt"));
                $this->_redirect('/administration_service_category');
            }
            else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * Edit restaurant category
     * @author alex
     * @since 15.12.2010
     */
    public function editAction(){
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration_service_category');
        }

        if (is_null($request->getParam('id', null))) {
            $this->error(__b("This category is non-existant"));
            $this->_redirect('/administration_service_category');
        }

        //create category object
        try {
            $category = new Yourdelivery_Model_Servicetype_Categories($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This category is non-existant"));
            $this->_redirect('/administration_service_category');
        }

        if ( $request->isPost() ){
            $post = $this->getRequest()->getPost();

            $form = new Yourdelivery_Form_Administration_Service_Category_Edit();
            if ( $form->isValid($post) ){
                $values = $form->getValues();

                //save new data
                $category->setData($values);
                $category->save();
                
                $this->success(__b("Changes successfully saved"));
                $this->_redirect('/administration_service_category');
            }
            else{
                $this->error($form->getMessages());
            }
        }

        $this->view->assign('category', $category);
    }

    /**
     * Delete restaurant category
     * @author alex
     * @since 15.12.2010
     */
     public function deleteAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('id', null))) {
            $this->error(__b("This category is non-existant"));
            $this->_redirect('/administration_service_category');
        }

        //create category object
        try {
            $category = new Yourdelivery_Model_Servicetype_Categories($request->getParam('id'));
        }
        catch ( Yourdelivery_Exception_Database_Inconsistency $e ){
            $this->error(__b("This category is non-existant"));
            $this->_redirect('/administration_service_category');
        }

        if(is_null($category->getId())) {
            $this->error(__b("This category is non-existant"));
        }
        else {
            $category->getTable()->remove($category->getId());
        }


        $this->_redirect('/administration_service_category');
    }
}