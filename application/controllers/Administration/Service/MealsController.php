<?php

/**
 * @author alex
 * @since 06.01.2011
 */
class Administration_Service_MealsController extends Default_Controller_AdministrationBase {

    /**
     * Batch management of meal pictures
     * @author alex
     * @since 06.01.2011
     */
    public function picturesbatchuploadAction() {
        $request = $this->getRequest();

        $searchtext = $request->getParam('searchtext');
        $exactphrase = $request->getParam('exactphrase');
        $excludetext = $request->getParam('excludetext');
        $excluderestaurants = $request->getParam('excluderestaurants');
        $onlyIfNoImage = $request->getParam('onlyifnoimage');
        $showOnlyIfNoImage = $request->getParam('showOnlyIfNoImage');

        // if no search text was entered, quit
        if (strlen(trim($searchtext)) == 0) {
            return;
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            // parse and normalize restaurants list
            $restIds = explode(",", $excluderestaurants);
            $restIdsNormalized = array();
            // sanity checks
            foreach ($restIds as $rid) {
                if (strlen($rid) < 10 && strlen($rid) > 0 && is_numeric($rid)) {
                    $restIdsNormalized[] = trim($rid);
                }
            }

            $excluderestaurants = implode(",", $restIdsNormalized);

            //we are setting new image for meals
            if (isset($post['loadimg']) || isset($post['loadimgselected'])) {
                set_time_limit(500);

                $form = new Yourdelivery_Form_Administration_Service_Meals_Upload();

                if ($form->isValid($post)) {
                    $values = $form->getValues();

                    if ($form->img->isUploaded()) {
                        $fn = $form->img->getFileName();

                        // fill array of meal ids depending on - if we are setting picture for all or only selected meals
                        $mealIds = array();
                        if (isset($post['loadimg'])) {
                            // find all meals, having this search string in it's name
                            $selectedMeals = Yourdelivery_Model_DbTable_Meals::getMealsWithSearchedString($searchtext, $exactphrase, $excludetext, $excluderestaurants, $showOnlyIfNoImage);
                        }
                        // we are setting image only for the selected meals
                        else {
                            $selectedMeals = $post['yd-id-checkbox'];
                        }

                        if (!is_null($selectedMeals) && is_array($selectedMeals)) {
                            foreach ($selectedMeals as $mealId => $val) {
                                $mealIds[] = $mealId;
                            }
                        }
                        
                        $countset = 0;
                        foreach ($mealIds as $mid) {
                            $meal = new Yourdelivery_Model_Meals($mid);

                            try {
                                if ($meal->setImg($form->img->getFileName(), $onlyIfNoImage, false)) {
                                    $countset++;
                                }
                            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                            }
                        }

                        $this->success(__b("Das neue Bild wurde für ") . $countset . __b(" Speisen gesetzt."));

                        // count of all meal for which the new image was set
                        $this->view->countset = $countset;
                    }
                } else {
                    $this->error($form->getMessages());
                }
            }
            //we are deleting image for meals
            if (isset($post['deleteimg']) || isset($post['deleteimgselected'])) {
                $mealIds = array();
                if (isset($post['deleteimg'])) {
                    // find all meals, having this search string in it's name
                    $selectedMeals = Yourdelivery_Model_DbTable_Meals::getMealsWithSearchedString($searchtext, $exactphrase, $excludetext, $excluderestaurants, $showOnlyIfNoImage);
                }
                // we are deleting images only for the selected meals
                else {
                    $selectedMeals = $post['yd-id-checkbox'];
                }

                if (!is_null($selectedMeals) && is_array($selectedMeals)) {
                    foreach ($selectedMeals as $mealId => $val) {
                        $mealIds[] = $mealId;
                    }
                }
                
                if (!is_null($selectedMeals) && is_array($selectedMeals)) {
                    $countset = 0;                    
                    foreach ($mealIds as $mid) {
                        $meal = new Yourdelivery_Model_Meals($mid);
                        try {
                            $meal->removeImg();
                            $countset++;
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                        }
                    }
                }

                $this->success(__b("Bilder wurden für %s Speisen gelöscht.", $countset));
            }
        }

        $grid = Default_Helper::getTableGrid();
        $grid->setRecordsPerPage(150);
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());

        if (strlen($excludetext) > 0) {
            $excludeCriterium = " AND m.name NOT LIKE '%" . $excludetext . "%'";
        }

        if (strlen($excluderestaurants) > 0) {
            $excludeIds = $teile = explode(",", $excluderestaurants);
            $excludeFilteredIds = array();
            foreach ($excludeIds as $eid) {
                if (strlen(trim($eid)) > 0 && is_int(trim($eid))) {
                    $excludeFilteredIds[] = trim($eid);
                }
            }

            if (count($excludeFilteredIds) > 0) {
                $excludeCriterium = " AND r.id NOT IN (" . implode(",", $excludeFilteredIds) . ")";
            }
        }

        if ($exactphrase == 1) {
            $criterium = 'm.deleted = 0 AND m.restaurantId>0 AND m.name = "' . $searchtext . '"' . $excludeCriterium;
        } else {
            $criterium = 'm.deleted = 0 AND m.restaurantId>0 AND m.name LIKE "%' . $searchtext . '%"' . $excludeCriterium;
        }

        if ($showOnlyIfNoImage == 1) {
            $criterium .= " AND m.hasPicture = 0 ";
        }

        //select meals
        $select = $db->select()->distinct()->from(array('m' => 'meals'), array(
                    __b('ID') => 'm.id',
                    __b('Name') => 'm.name',
                    __b('Beschreibung') => 'm.description',
                    __b('Status') => 'm.status',
                ))
                ->join(array('cat' => 'meal_categories'), 'cat.id=m.categoryId', array(__b('Kategorie') => 'cat.name'))
                ->join(array('r' => 'restaurants'), 'r.id=m.restaurantId', array(__b('Restaurant') => 'r.name', 'RID' => 'r.id', __b('Franchise') => 'r.franchiseTypeId'))
                ->where($criterium)
                ->order('r.id')
                ->order('cat.name')
                ->order('m.name');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn(__b('RID'), array('hidden' => 1));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('RID') . '}}">{{' . __b('Restaurant') . '}}</a> <a href="/administration/servicelogin/id/{{' . __b('RID') . '}}">' . __b('Login') . '</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Franchise'), array('callback' => array('function' => 'getFranchise', 'params' => array('{{' . __b('Franchise') . '}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $activeStatis = array(
            '1' => __b('online'),
            '0' => __b('offline'),
            '' => __b('Alle')
        );

        $yesNoStates = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle')
        );

        $franchises = Yourdelivery_Model_Servicetype_Franchise::all();
        $franchiseType = array('' => __b('Alle'));
        foreach ($franchises as $franchise) {
            $franchiseType[$franchise['id']] = $franchise['name'];
        }

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Franchise'), array('values' => $franchiseType))
                ->addFilter(__b('Beschreibung'))
                ->addFilter(__b('Status'), array('values' => $activeStatis))
                ->addFilter(__b('Kategorie'), array('values' => $categories));
        $grid->addFilters($filters);

        // meal picture
        $picture = new Bvb_Grid_Extra_Column();
        $picture->position('right')->name('Bilder')->callback(array('function' => 'showMealPicture', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('RID') . '}}')));

        $idCheckboxes = new Bvb_Grid_Extra_Column();
        $idCheckboxes->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{' . __b('ID') . '}}')));

        //add extra rows
        $grid->addExtraColumns($picture, $idCheckboxes);

        //deploy grid to view
        $this->view->grid = $grid->deploy();

        $this->view->searchtext = $searchtext;
        $this->view->exactphrase = $exactphrase;
        $this->view->excludetext = $excludetext;
        $this->view->excluderestaurants = $excluderestaurants;
        $this->view->showOnlyIfNoImage = $showOnlyIfNoImage;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 25.06.2012
     * @return array 
     */
    protected function _getSearch() {

        $search = null;
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $search = $this->session->serviceMealsSearch;
        }

        if (!is_array($search)) {
            $criteria = $request->getParam('criteria', array());
            $value = $request->getParam('value', array(""));
            $inName = $request->getParam('inName', false);
            $inDescription = $request->getParam('inDescription', false);
            $inCategory = $request->getParam('inCategory', false);
            $withoutCategory = $request->getParam('withoutCategory', false);
            $records = $request->getParam('records', 25);

            if (!$inName && !$inDescription && !$inCategory) {
                $inName = true;
            }

            $search = array(
                'criteria' => is_array($criteria) ? $criteria : array(),
                'value' => is_array($value) ? $value : array(),
                'inName' => (boolean) $inName,
                'inDescription' => (boolean) $inDescription,
                'inCategory' => (boolean) $inCategory,
                'withoutCategory' => (boolean) $withoutCategory,
                'records' => (integer) $records,
            );

            $this->session->serviceMealsSearch = $search;
        }

        if ($request->isPost()) {
            if (!strlen(trim(implode("", $search['value'])))) {
                $this->error(__b("Sie haben keine Suchbegriffe definiert!"));
            }
        }

        return $search;
    }

    /**
     * @author Alex Vait <vait@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 29.06.2011, 25.06.2012
     */
    public function ingredientsAction() {

        // set grid to view
        $this->view->search = $search = $this->_getSearch();
        $this->view->grid = $this->_getGrid($search);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 29.06.2011, 25.06.2012
     */
    public function typesAction() {

        // set grid to view
        $this->view->search = $search = $this->_getSearch();
        $this->view->grid = $this->_getGrid($search);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 19.07.2011, 25.06.2012
     * @return string
     */
    protected function _getGrid($search) {

        if (!strlen(trim(implode("", $search['value'])))) {
            return "";
        }

        // create grid with meals
        $grid = Default_Helper::getTableGrid();
        $grid->setRecordsPerPage($search['records']);
        $grid->setExport(array());

        // build select
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->distinct()
                ->from(array('m' => 'meals'), array(
                    __b('ID') => 'm.id',
                    __b('Name') => 'm.name',
                    __b('Beschreibung') => 'm.description',
                    __b('Status') => 'm.status',
                    __b('Kategorien') => new Zend_Db_Expr('GROUP_CONCAT(mt.name)'),
                    __b('Zutaten') => new Zend_Db_Expr('GROUP_CONCAT(mi.name)'),
                    __b('Attribute') => 'm.attributes',
                ))
                ->join(array('cat' => 'meal_categories'), 'cat.id = m.categoryId', array(
                    __b('Speisekategorie') => 'cat.name',
                ))
                ->join(array('r' => 'restaurants'), 'r.id = m.restaurantId', array(
                    __b('Restaurant') => 'r.name',
                    __b('RID') => 'r.id',
                ))
                ->joinLeft(array('mtn' => 'meal_types_nn'), 'mtn.mealId = m.id', array())
                ->joinLeft(array('mt' => 'meal_types'), 'mtn.typeId = mt.id', array())
                ->joinLeft(array('min' => 'meal_ingredients_nn'), 'min.mealId = m.id', array())
                ->joinLeft(array('mi' => 'meal_ingredients'), 'min.ingredientId = mi.id', array())
                ->where("m.deleted = 0")
                ->where("m.restaurantId > 0")
                ->group('m.id')
                ->order('r.id')
                ->order('cat.name')
                ->order('m.name');

        if ($search['withoutCategory']) {
            $select->where("mt.id IS NULL");
        }

        foreach ($search['value'] as $k => $value) {
            if (empty($value)) {
                continue;
            }

            $criteria = "= ?";
            switch ($search['criteria'][$k]) {
                case 'notequals';
                    $criteria = "!= ?";
                    break;

                case 'like';
                    $criteria = "LIKE ?";
                    $value = "%" . $value . "%";
                    break;

                case 'notlike';
                    $criteria = "NOT LIKE ?";
                    $value = "%" . $value . "%";
                    break;

                case 'startwith';
                    $criteria = "LIKE ?";
                    $value = "%" . $value;
                    break;

                case 'endwith';
                    $criteria = "LIKE ?";
                    $value = $value . "%";
                    break;
            }

            $search['inName'] ? $select->where("m.name " . $criteria, $value) : null;
            $search['inDescription'] ? $select->where("m.description " . $criteria, $value) : null;
            $search['inCategory'] ? $select->where("cat.name " . $criteria, $value) : null;
        }

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('RID'), array('hidden' => 1));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('RID') . '}}" target="_blank">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Kategorien'), array('decorator' => '<span id="yd-meals-grid-types-{{ID}}">{{' . __b('Kategorien') . '}}</span>', 'callback' => array('function' => 'getMealTypesHierarchy', 'params' => array('{{ID}}'))));
        $grid->updateColumn(__b('Zutaten'), array('decorator' => '<span id="yd-meal-grid-ingredients-{{ID}}">{{' . __b('Zutaten') . '}}</span>'));
        $grid->updateColumn(__b('Attribute'), array('decorator' => '<span id="yd-meal-grid-attributes-{{ID}}">{{' . __b('Attribute') . '}}</span>', 'callback' => array('function' => 'getMealAttributes', 'params' => array('{{ID}}'))));

        // add filters
        $filters = new Bvb_Grid_Filters();
        $activeStatis = array(
            '1' => __b('online'),
            '0' => __b('offline'),
            '' => __b('Alle')
        );

        // add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Beschreibung'))
                ->addFilter(__b('Status'), array('values' => $activeStatis))
                ->addFilter(__b('Speisekategorie'));
        $grid->addFilters($filters);

        // add checkoxes
        $idCheckboxes = new Bvb_Grid_Extra_Column();
        $idCheckboxes->position('left')
                ->name('')
                ->callback(array('function' => 'idCheckbox', 'params' => array('{{' . __b('ID') . '}}')));
        $grid->addExtraColumns($idCheckboxes);

        return $grid->deploy();
    }

}
