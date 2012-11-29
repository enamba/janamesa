<?php

/**
 * Description of RestaurantController
 *
 * @author vait
 */
class RestaurantController extends Default_Controller_RestaurantBase {

    /**
     * show main site with a list of all orders
     */
    public function indexAction() {
        $restaurant = $this->initRestaurant();
        if (!is_object($restaurant) || is_null($restaurant->getId())) {
            $this->_redirect('/login');
        }
    }

    /**
     * show list of all cvategories, where the user can add new category
     * or change the order of categories, in which they appear in the menu
     */
    public function mealcategoriesAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some meal category
        $path = $this->getRequest()->getPathInfo();
        $this->session->mealcategoriespath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select categories
        $select = $db->select()->distinct()->from(array('c' => 'meal_categories'), array(
                'ID' => 'c.id',
                __b('Name') => 'c.name',
                __b('Beschreibung') => 'c.description',
                __b('Mwst') => 'c.mwst',
                __b('Pfand') => 'c.hasPfand',
                'exclMincost' => 'c.excludeFromMinCost',
                'c.main',
                __b('Rank') => 'c.rank',
                __b('Servicetype') => 'c.id',
                __b('Speisen') => 'c.id'
            ))
            ->where('c.restaurantId = ' . $restaurant->getId())
            ->order('c.rank ASC');


        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        //$grid->updateColumn('ID',array('hidden'=>1));
        $grid->updateColumn(__b('Pfand'), array('callback' => array('function' => 'intToYesNo', 'params' => array('{{' . __b('Pfand') . '}}'))));
        $grid->updateColumn(__b('Standard'), array('callback' => array('function' => 'intToYesNo', 'params' => array('{{' . __b('Standard') . '}}'))));
        $grid->updateColumn('exclMincost', array('callback' => array('function' => 'intToYesNoReverse', 'params' => array('{{exclMincost}}')), 'title' => __b('In Mindestbestellwert')));
        $grid->updateColumn('main', array('callback' => array('function' => 'intToYesNo', 'params' => array('{{main}}')), 'title' => __b('Voreingestellt')));
        $grid->updateColumn(__b('Servicetype'), array('title' => __b('Dienstleister Typ'), 'callback' => array('function' => 'getServiceTypes', 'params' => array('{{ID}}'))));
        $grid->updateColumn(__b('Speisen'), array('callback' => array('function' => 'getMealsForCategory', 'params' => array('{{ID}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();

        $yesnoVals = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle'),
        );

        $yesnoReverseVals = array(
            '1' => __b('Nein'),
            '0' => __b('Ja'),
            '' => __b('Alle'),
        );

        //add filters
        $filters->addFilter(__b('Name'))
            ->addFilter(__b('Beschreibung'))
            ->addFilter(__b('Pfand'), array('values' => $yesnoVals))
            ->addFilter('main', array('values' => $yesnoVals))
            ->addFilter(__b('exclMincost'), array('values' => $yesnoReverseVals));

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $confirm = __b("Soll diese Kategorie wirklich dupliziert werden?");
        $option->position('right')->name(__b("Options"))->decorator(
            // Please DO NOT INDENT the closing nowdoc tag HTML
            <<<HTML
                    <div align="center">
                        <a name="cat_{{ID}}"></a>
                        <a href="/restaurant_categories/rankup/id/{{ID}}">&uarr;</a>&nbsp;&nbsp;&nbsp;
                        <a href="/restaurant_categories/rankdown/id/{{ID}}">&darr;</a><br/><br/>
                        <a href="/restaurant_categories/duplicate/id/{{ID}}" onclick="javascript:return confirm('$confirm')">&Delta;</a>
                    </div>
HTML
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show the list of all meals and form for adding a new meal with only bsic informations
     */
    public function mealsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        // Creating grid with non-empty defaults
        $request = $this->getRequest();
        $grid = Default_Helper::getTableGrid('grid', array(
            __b('Gelöscht') => '0'
        ), $request);

        //set path so the sorting and filtering will stay when we edit some meal
        $path = $request->getPathInfo();
        $this->session->mealspath = $path;

        if ($request->isPost()) {
            $post = $request->getPost();
            //selected category changed, update the list of sizes
            if (!is_null($post['categoryId']) && $post['categoryId'] != -1) {
                $category = new Yourdelivery_Model_Meal_Category($post['categoryId']);
                $sizes = $category->getSizes();

                $this->view->assign('category_mwst', $category->getMwst());
                $this->view->assign('sizes', $sizes);
                $this->view->assign('categoryId', $category->getId());
            }
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $grid->setExport(array());
        //select meal sizes
        $select = $db->select()->distinct()->from(array('m' => 'meals'), array(
                'ID' => 'm.id',
                __b('Name') => 'm.name',
                __b('Beschreibung') => 'm.description',
                __b('Interne Nummer') => 'm.nr',
                __b('Status') => 'm.status',
                __b('Mwst') => 'm.mwst',
                __b('Art') => 'm.attributes',
                __b('Tabak') => 'm.tabaco',
                __b('Min') => 'm.minAmount',
                __b('Rank') => 'm.rank',
                __b('Gelöscht') => 'm.deleted',
            ))
            ->joinLeft(array('cat' => 'meal_categories'), 'cat.id=m.categoryId', array(__b('Kategorie') => 'cat.name', 'CID' => 'cat.id'))
            ->where('m.restaurantId = ' . $restaurant->getId())
            ->order('cat.rank')
            ->order('m.rank');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(30);

        $grid->updateColumn('CID', array('hidden' => 1));
        $grid->updateColumn(__b('Kategorie'), array('searchType' => 'equal', 'decorator' => '<a href="/restaurant_categories/edit/id/{{CID}}">{{' . __b('Kategorie') . '}}</a>'));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Interne Nummer'), array('searchType' => "="));
        $grid->updateColumn(__b('Art'), array('callback' => array('function' => 'Default_Helpers_Grid::getAtrributeReadable', 'params' => array('{{' . __b('Art') . '}}'))));
        $grid->updateColumn(__b('Tabak'), array('callback' => array('function' => 'intToYesNo', 'params' => array('{{' . __b('Tabak') . '}}'))));
        $grid->updateColumn(__b('Min'), array('title' => __b('Min. Anzahl')));
        $grid->updateColumn(__b('Gelöscht'), array('callback' => array('function' => 'deletedToReadable', 'params' => array('{{' . __b('Gelöscht') . '}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $activeStatis = array(
            '1' => __b('online'),
            '0' => __b('offline')
        );
        $activeStatis[''] = __b('Alle');

        $deletedStatis = array(
            '%' => __b('Alle'),             // all, but not as default
            '0' => __b('nicht gelöscht'),
            '1' => __b('gelöscht')
        );

        $yesno = array(
            '1' => __b('ja'),
            '0' => __b('nein')
        );
        $yesno[''] = __b('Alle');

        $categories = array();
        foreach (Yourdelivery_Model_DbTable_Meal_Categories::getCategories($restaurant->getId()) as $c) {
            $categories[$c['name']] = $c['name'];
        }
        $categories[''] = __b('Alle');

        //add filters
        $filters->addFilter('ID')
            ->addFilter(__b('Name'))
            ->addFilter(__b('Beschreibung'))
            ->addFilter(__b('Interne Nummer'))
            ->addFilter(__b('Status'), array('values' => $activeStatis))
            ->addFilter(__b('Art'), array('values' => Yourdelivery_Model_Meals::getAllAttributes()))
            ->addFilter(__b('Tabak'), array('values' => $yesno))
            ->addFilter(__b('Min'))
            ->addFilter(__b('Gelöscht'), array('values' => $deletedStatis))
            ->addFilter(__b('Kategorie'), array('values' => $categories));
        $grid->addFilters($filters);

        // meal picture
        $picture = new Bvb_Grid_Extra_Column();
        $picture->position('right')->name(__b('Bilder'))->callback(array('function' => 'Default_Helpers_Grid_Restaurant::mealPictureOption', 'params' => array('{{ID}}', $restaurant->getId())));

        $grid->addExtraColumns($picture);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show the meal sizes list
     */
    public function mealsizesAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some meal size
        $path = $this->getRequest()->getPathInfo();
        $this->session->mealsizespath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select meal sizes
        $select = $db->select()->distinct()->from(array('s' => 'meal_sizes'), array(
                'ID' => 's.id',
                __b('Name') => 's.name',
                __b('Beschreibung') => 's.description',
                __b('Rank') => 's.rank'
            ))
            ->join(array('cat' => 'meal_categories'), 'cat.id=s.categoryId', array(
                __b('Kategorie') => 'cat.name', 'CID' => 'cat.id'
            ))
            ->where('cat.restaurantId = ' . $restaurant->getId())
            ->order('s.categoryId ASC')
            ->order('s.rank');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(50);

        $grid->updateColumn('CID', array('hidden' => 1));
        $grid->updateColumn('Status', array('callback' => array('function' => 'statusToReadable', 'params' => array('{{Status}}'))));
        $grid->updateColumn(__b('Kategorie'), array('decorator' => '<a href="/restaurant_categories/edit/id/{{CID}}">{{' . __b('Kategorie') . '}}</a>'));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $activeStatis = array(
            '0' => __b('online'),
            '1' => __b('offline')
        );
        $activeStatis[''] = __b('Alle');

        $categories = array();
        foreach (Yourdelivery_Model_DbTable_Meal_Categories::getCategories($restaurant->getId()) as $c) {
            $categories[$c['name']] = $c['name'];
        }
        $categories[''] = __b('Alle');

        //add filters
        $filters->addFilter(__b('Name'))
            ->addFilter(__b('ID'))
            ->addFilter(__b('Beschreibung'))
            ->addFilter(__b('Kategorie'))
            ->addFilter(__b('Kategorie'), array('values' => $categories))
            ->addFilter(__b('Status'), array('values' => $activeStatis));

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Optionen'))->decorator(
            <<<HTML
                    <div>
                        <a name="size_{{ID}}"></a>
                        <a href="/restaurant_sizes/sizeleft/id/{{ID}}">&uarr;</a>&nbsp;&nbsp;
                        <a href="/restaurant_sizes/sizeright/id/{{ID}}">&darr;</a>
                        <a href="/restaurant_sizes/edit/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                    </div>
HTML
        );
        //add extra rows
        $grid->addExtraColumns($option);

        // save category from previos meal size
        $request = $this->getRequest();
        $selectedCategoryId = $request->getParam("cat", null);
        if (!is_null($selectedCategoryId)) {
            $this->view->assign('selectedCategoryId', $selectedCategoryId);
        }

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /*
     * show meal extras of this restaurant
     * @author Alex Vait 
     * @since 23.07.2012
     */
    public function mealextrasAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some extras
        $path = $this->getRequest()->getPathInfo();
        $this->session->extrasspath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select categories
        $select = $db->select()->from(array('me' => 'meal_extras'), array(
                'ID' => 'me.id',
                __b('Name') => 'me.name',
                __b('Status') => 'me.status',
                __b('Mwst') => 'me.mwst',
                __b('Zuordnungen') => new Zend_Db_Expr('(select count(id)>0 from meal_extras_relations where extraId=me.id)'),
            ))
            ->joinLeft(array('mg' => 'meal_extras_groups'), 'me.groupId=mg.id', array('GID' => 'mg.id', 'GName' => new Zend_Db_Expr('CONCAT(coalesce(mg.name,""), "(", mg.internalName , ")")')))
            ->where('mg.restaurantId = ' . $restaurant->getId())
            ->order('mg.name ASC')
            ->order('me.name ASC');
        
        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(200);

        $grid->updateColumn('GName', array('hidden' => 1));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'onlineStatus', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Zuordnungen'), array('callback' => array('function' => 'extrasMealRelations', 'params' => array('{{' . __b('Zuordnungen') . '}}', '{{ID}}')), 'title' => __b('Zuordnungen zu Speisen')));
        $grid->updateColumn(__b('Kategorie'), array('decorator' => '<a href="/restaurant_categories/edit/id/{{CID}}">{{' . __b('Kategorie') . '}}</a>'));
        $grid->updateColumn('GID', array('decorator' => '<a href="/restaurant_extras/editgroup/id/{{GID}}">{{GName}}</a>', 'title' => __b('Gruppe')));

        $filters = new Bvb_Grid_Filters();
        //translate stati
        $statis = array(
            '0' => __b('online'),
            '1' => __b('offline')
        );
        $statis[''] = __b('Alle');

        $mealRelations = array(
            '0' => __b('Keine'),
            '1' => __b('Vorhanden')
        );
        $mealRelations[''] = __b('Alle');

        $extrasgroups = array();
        foreach (Yourdelivery_Model_DbTable_Meal_ExtrasGroups::getAllExtrasGroupsNames($restaurant->getId()) as $g) {
            $extrasgroups[$g['id']] = sprintf('%s(%s)',$g['name'],$g['internalName']);
        }
        $extrasgroups[''] = __b('Alle');

        //add filters
        $filters->addFilter('ID');
        $filters->addFilter(__b('Name'));
        $filters->addFilter(__b('Status'), array('values' => $statis));
        $filters->addFilter(__b('Zuordnungen'), array('values' => $mealRelations));
        $filters->addFilter('GID', array('values' => $extrasgroups));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $confirm = __b("Soll diese Extra wirklich gelöscht werden?");

        $option->position('right')->name(__b('Options'))->decorator(
            <<<HTML
                    <div>
                        <a href="/restaurant_extras/edit/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                        <a href="/restaurant_extras/delete/id/{{ID}}" onclick="javascript:return confirm('$confirm')"><img src="/media/images/yd-backend/del-cat.gif"/></a>&nbsp;<br>
                    </div>
HTML
        );

        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
        $this->view->all_extras = Yourdelivery_Model_Meal_Extra::getDistinctExtrasNames();
    }

    /**
     * show meal extras groups
     */
    public function mealextrasgroupsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some extras group
        $path = $this->getRequest()->getPathInfo();
        $this->session->extrasgroupsspath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select categories
        $select = $db->select()->from(array('mg' => 'meal_extras_groups'), array(
                'ID' => 'mg.id',
                __b('Name im Frontend') => 'mg.name',
                __b('Interner Name') => 'mg.internalName',
                __b('Extras') => 'mg.id',
            ))
            ->where('mg.restaurantId = ' . $restaurant->getId())
            ->order('mg.name ASC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(30);

        $grid->updateColumn(__b('Extras'), array('callback' => array('function' => 'getExtrasForGroup', 'params' => array('{{ID}}'))));
        //$grid->updateColumn('ID',array('hidden'=>1));
        //add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('ID')
            ->addFilter(__b('Name im Frontend'))
            ->addFilter(__b('Interner Name'));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $confirm = __b("Soll diese Gruppe wirklich gelöscht werden?");

        $option->position('right')->name(__b('Optionen'))->decorator(
            <<<HTML
                    <div>
                        <a href="/restaurant_extras/editgroup/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                        <a href="/restaurant_extras/deletegroup/id/{{ID}}" onclick="javascript:return confirm('$confirm')"><img src="/media/images/yd-backend/del-cat.gif"/></a>&nbsp;<br>
                    </div>
HTML
        );

        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show meal options
     */
    public function mealoptionsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some options
        $path = $this->getRequest()->getPathInfo();
        $this->session->optionspath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select categories
        $select = $db->select()->distinct()->from(array('mo' => 'meal_options'), array(
                'ID' => 'mo.id',
                __b('Name') => 'mo.name',
                __b('Preis') => 'mo.cost',
                __b('Status') => 'mo.status',
                __b('Mwst') => 'mo.mwst',
                __b('Gruppe') => 'mo.id',
            ))
            ->join(array('optNN' => 'meal_options_nn'), 'optNN.optionId=mo.id', array('NID' => 'optNN.id'))
            ->join(array('optRow' => 'meal_options_rows'), 'optNN.optionRowId=optRow.id', array('RID' => 'optRow.id'))
            ->joinLeft(array('cat' => 'meal_categories'), 'cat.id=optRow.categoryId', array(__b('Kategorie') => 'cat.name', 'CID' => 'cat.id'))
            ->where('cat.restaurantId = ' . $restaurant->getId() . ' or optRow.restaurantId = ' . $restaurant->getId())
            ->order('mo.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(30);

        $grid->updateColumn('CID', array('hidden' => 1));
        $grid->updateColumn('RID', array('hidden' => 1));
        $grid->updateColumn('NID', array('hidden' => 1));
        $grid->updateColumn(__b('Preis'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Preis') . '}}'))));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'onlineStatus', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Gruppe'), array('callback' => array('function' => 'getOptionsGroups', 'params' => array('{{ID}}'))));

        $filters = new Bvb_Grid_Filters();
        //translate stati
        $statis = array(
            '0' => __b('online'),
            '1' => __b('offline')
        );
        $statis[''] = __b('Alle');

        //add filters
        $filters->addFilter('ID');
        $filters->addFilter(__b('Name'));
        $filters->addFilter(__b('Preis'));
        $filters->addFilter(__b('Status'), array('values' => $statis));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $confirm = __b("Soll diese Option wirklich gelöscht werden?");

        $option->position('right')->name(__b('Optionen'))->decorator(
            <<<HTML
                    <div>
                        <a href="/restaurant_options/edit/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                        <a href="/restaurant_options/delete/id/{{ID}}" onclick="javascript:return confirm('$confirm')"><img src="/media/images/yd-backend/del-cat.gif"/></a>&nbsp;<br>
                    </div>
HTML
        );

        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show meal options groups
     */
    public function mealoptionrowsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        //set path so the sorting and filtering will stay when we edit some options groups
        $path = $this->getRequest()->getPathInfo();
        $this->session->optionrowsspath = $path;

        $formOptionRow = new Yourdelivery_Form_Restaurant_MealOptionsRowEdit();
        $formOptionRow->setService($restaurant);
        $formOptionRow->removeElement('rank');
        $this->view->formOptionRow = $formOptionRow;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select categories
        $select = $db->select()->from(array('mg' => 'meal_options_rows'), array(
                'ID' => 'mg.id',
                __b('Name im Frontend') => 'mg.name',
                __b('Interner Name') => 'mg.internalName',
                __b('Beschreibung') => 'mg.description',
                __b('Auswahl') => 'mg.choices',
                __b('Rang') => 'mg.rank',
                __b('Optionen') => 'mg.id',
                __b('Zugeordnete Speisen') => 'mg.id',
            ))
            ->joinLeft(array('cat' => 'meal_categories'), 'cat.id=mg.categoryId', array(__b('Kategorie') => 'cat.name', 'CID' => 'cat.id'))
            ->where('cat.restaurantId = ' . $restaurant->getId() . ' or mg.restaurantId = ' . $restaurant->getId())
            ->order('mg.name ASC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(30);

        $grid->updateColumn('CID', array('hidden' => 1));
        $grid->updateColumn(__b('Optionen'), array('callback' => array('function' => 'getOptionsForGroup', 'params' => array('{{ID}}'))));
        $grid->updateColumn(__b('Zugeordnete Speisen'), array('callback' => array('function' => 'getMealsForOptionsGroup', 'params' => array('{{ID}}')), 'title' => __b('Zugeordnete Speisen aus anderen Kategorien')));

        //add filters
        $filters = new Bvb_Grid_Filters();

        $categories = array();
        foreach (Yourdelivery_Model_DbTable_Meal_Categories::getCategories($restaurant->getId()) as $c) {
            $categories[$c['name']] = $c['name'];
        }
        $categories[''] = __b('Alle');

        //add filters
        $filters->addFilter('ID')
            ->addFilter(__b('Name im Frontend'))
            ->addFilter(__b('Interner Name'))
            ->addFilter(__b('Beschreibung'))
            ->addFilter(__b('Auswahl'))
            ->addFilter(__b('Kategorie'), array('values' => $categories));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $confirm = __b("Soll diese Gruppe wirklich gelöscht werden?");

        $option->position('right')->name(__b('Bearbeiten'))->decorator(
            <<<HTML
                    <div>
                        <a href="/restaurant_options/editgroup/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                        <a href="/restaurant_options/deletegroup/id/{{ID}}" onclick="javascript:return confirm('$confirm')"><img src="/media/images/yd-backend/del-cat.gif"/></a>&nbsp;<br>
                    </div>
HTML
        );

        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'idCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all orders
     * @author vpriem
     * @since 13.10.2010
     */
    public function ordersAction() {

        $restaurant = $this->initRestaurant();
        if ($restaurant->getId() === null) {
            $this->_redirect('/index');
        }
        $this->view->assign('nav_active', 'orders');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
            ->select()
            ->distinct()
            ->from(array('o' => 'orders'), array(
                'ID' => 'o.id',
                __b('Nummer') => 'o.nr',
                __b('Eingang') => new Zend_Db_Expr('DATE_FORMAT(o.time, "%d.%m.%Y %H:%i")'),
                __b('Lieferzeit') => new Zend_Db_Expr('DATE_FORMAT(o.deliverTime, "%d.%m.%Y %H:%i")'),
                __b('Status') => 'o.state',
                __b('Payment') => 'o.payment',
                __b('Typ') => 'o.mode',
                __b('Kundentyp') => 'o.kind',
                __b('Preis') => new Zend_Db_Expr('o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount'),
                __b('Pfand') => 'o.pfand',
            ))
            ->join(array('ocn' => 'orders_customer'), 'ocn.orderId = o.id', array(
                __b('Name') => new Zend_Db_Expr('CONCAT(ocn.prename, " ", ocn.name)'),
                __b('eMail') => 'ocn.email'
            ))
            ->join(array('l' => 'orders_location'), 'l.orderId = o.id', array(
                __b('Straße') => new Zend_Db_Expr('CONCAT(l.street, " ", l.hausnr, " ", l.plz)'),
                __b('Firma') => 'l.companyName'
            ))
            ->where('o.restaurantId = ?', $restaurant->getId())
            ->order('o.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn(__b('eMail'), array('decorator' => '<a href="mailto:{{' . __b('eMail') . '}}">{{' . __b('eMail') . '}}</a>'));
        $grid->updateColumn(__b('Preis'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Preis') . '}}'))));
        $grid->updateColumn(__b('Pfand'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Pfand') . '}}'))));
        $grid->updateColumn(__b('Status'), array('searchType' => 'equal', 'class' => 'status', 'callback' => array('function' => 'intToStatusOrders', 'params' => array('{{' . __b('Status') . '}}', '{{' . __b('Typ') . '}}'))));
        $grid->updateColumn(__b('Typ'), array('callback' => array('function' => 'modeToReadable', 'params' => array('{{' . __b('Typ') . '}}'))));
        $grid->updateColumn(__b('Kundentyp'), array('callback' => array('function' => 'kindToReadable', 'params' => array('{{' . __b('Kundentyp') . '}}'))));

        // translate stati
        $statis = array(
            '-5' => __b('Prepayment'),
            '-4' => __b('Unbestätigte Bestellung auf Rechnung'),
            '-3' => __b('Fake'),
            '-2' => __b('Storniert'),
            '-1' => __b('Fehlerhaft'),
            '0' => __b('Unbestätigt'),
            '1' => __b('Bestätigt'),
            '2' => __b('Ausgeliefert'),
            '' => __b('Alle')
        );
        // translate payment
        $payments = array(
            'debit' => __b('Lastschrift'),
            'credit' => __b('Kreditkarte'),
            'paypal' => __b('PayPal'),
            'bar' => __b('Barzahlung'),
            'bill' => __b('Rechnung'),
            '' => __b('Alle')
        );

        // translate mode
        $modes = array(
            'rest' => __b('Restaurant'),
            'cater' => __b('Catering'),
            'fruit' => __b('Obst'),
            'great' => __b('Großhandel'),
            'canteen' => __b('Kantine'),
            '' => __b('Alle')
        );

        // translate customer type
        $kinds = array(
            'priv' => __b('Privat'),
            'comp' => __b('Firma'),
            '' => __b('Alle')
        );

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
            ->addFilter('ID')
            ->addFilter(__b('Status'), array('values' => $statis))
            ->addFilter(__b('Payment'), array('values' => $payments))
            ->addFilter(__b('Typ'), array('values' => $modes))
            ->addFilter(__b('Kundentyp'), array('values' => $kinds))
            ->addFilter(__b('Eingang'))
            ->addFilter(__b('Lieferzeit'))
            ->addFilter(__b('eMail'))
            ->addFilter(__b('Name'))
            ->addFilter(__b('Straße'))
            ->addFilter(__b('Firma'))
            ->addFilter(__b('Nummer'));
        $grid->addFilters($filters);

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option
            ->position('right')
            ->name(__b('Optionen'))
            ->callback(array('function' => 'optionsForOrdersRestaurant', 'params' => array('{{ID}}', '{{' . Typ . '}}')));
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /*
     * show the locations where this restaurant is delivering
     */

    public function locationsAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }
        $this->view->assign('restaurant', $restaurant);

        //set path so the sorting and filtering will stay when we edit some location
        $path = $this->getRequest()->getPathInfo();
        $this->session->locationspath = $path;

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        //select orders
        $select = $db->select()->distinct()->from(array('p' => 'restaurant_plz'), array(
                'ID' => 'p.id',
                __b('Stadt') => 'c.city',
                __b('Postleitzahl') => 'p.plz',
                __b('Lieferzeit') => 'p.deliverTime',
                __b('Mindestbestellwert') => 'p.mincost',
                __b('Lieferkosten') => 'p.delcost',
                'ndca' => 'p.noDeliverCostAbove',
                __b('Status') => 'p.status',
                __b('Kommentar') => 'p.comment'
            ))
            ->join(array('c' => 'city'), 'p.cityId=c.id', array())
            ->where('p.restaurantId = ' . $restaurant->getId())
            ->order('p.plz ASC');


        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setPagination(300);

        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn(__b('Lieferkosten'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Lieferkosten') . '}}'))));
        $grid->updateColumn('ndca', array('title' => __b('Keine Lieferkosten ab'), 'callback' => array('function' => 'intToPrice', 'params' => array('{{ndca}}'))));
        $grid->updateColumn(__b('Mindestbestellwert'), array('callback' => array('function' => 'intToPrice', 'params' => array('{{' . __b('Mindestbestellwert') . '}}'))));
        $grid->updateColumn(__b('Status'), array('class' => 'status', 'callback' => array('function' => 'onlineStatus', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Lieferzeit'), array('callback' => array('function' => 'secToMinutes', 'params' => array('{{' . __b('Lieferzeit') . '}}'))));

        $filters = new Bvb_Grid_Filters();

        $activeStatis = array(
            '0' => __b('offline'),
            '1' => __b('online')
        );
        $activeStatis[''] = __b('Alle');

        //add filters
        $filters->addFilter(__b('Postleitzahl'));
        $filters->addFilter(__b('Stadt'));
        $filters->addFilter(__b('Lieferzeit'));
        $filters->addFilter(__b('Mindestbestellwert'));
        $filters->addFilter(__b('Lieferkosten'));
        $filters->addFilter('ndca');
        $filters->addFilter(__b('Kommentar'));
        $filters->addFilter(__b('Status'), array('values' => $activeStatis));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $confirm = __b("Soll diese Postleitzahl wirklich gelöscht werden?");

        $option->position('right')->name(__b('Optionen'))->decorator(
            <<<HTML
                    <div>
                        <a href="/restaurant_locations/edit/id/{{ID}}"><img src="/media/images/yd-backend/cust_edit.png"/></a>&nbsp;
                        <a href="/restaurant_locations/delete/id/{{ID}}" onclick="javascript:return confirm('$confirm')"><img src="/media/images/yd-backend/del-cat.gif"/></a>
                    </div>
HTML
        );

        $optionCheckbox = new Bvb_Grid_Extra_Column();
        $optionCheckbox->position('left')->name('')->callback(array('function' => 'locationsCheckbox', 'params' => array('{{ID}}')));

        //add extra rows
        $grid->addExtraColumns($option, $optionCheckbox);

        //deploy grid to view
        $this->view->grid = $grid->deploy();

        $this->view->assign('ranges', $restaurant->getRanges());
        $this->view->assign('nav_active', 'location');
    }

    public function statsAction() {
        set_time_limit(200);

        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        if (date('w', time()) == 1) {
            $firstDayOfWeek = mktime(0, 0, 0);
        } else {
            $firstDayOfWeek = strtotime('last monday');
        }

        $firstDayOfMonth = mktime(0, 0, 0, date('n', time()), 1);
        $firstDayOfYear = mktime(0, 0, 0, 1, 1);

        $this->view->assign('orderstoday', $restaurant->getOrdersCalendar(mktime(0, 0, 0)));
        $this->view->assign('ordersweek', $restaurant->getOrdersCalendar($firstDayOfWeek));
        $this->view->assign('ordersmonth', $restaurant->getOrdersCalendar($firstDayOfMonth));
        $this->view->assign('ordersyear', $restaurant->getOrdersCalendar($firstDayOfYear));
        $this->view->assign('ordersall', $restaurant->getOrdersCalendar());

        $this->view->assign('salestoday', $restaurant->getSalesCalendar(mktime(0, 0, 0)));
        $this->view->assign('salesweek', $restaurant->getSalesCalendar($firstDayOfWeek));
        $this->view->assign('salesmonth', $restaurant->getSalesCalendar($firstDayOfMonth));
        $this->view->assign('salesyear', $restaurant->getSalesCalendar($firstDayOfYear));
        $this->view->assign('salesall', $restaurant->getSalesCalendar());
    }

    /**
     * show a sortable, filterable table of all restaurant billings
     */
    public function billingAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);

        //select orders
        $select = $db->select()->from(array('b' => 'billing'), array(
                'ID' => 'id',
                __b('Nummer') => 'number',
                __b('Betrag') => 'amount',
                __b('Von') => from,
                __b('Bis') => until,
                __b('Status') => 'status'
            ))
            ->join(array('r' => 'restaurants'), 'b.refId=r.id', array(
                'CID' => 'r.id',
                'FAX' => 'r.fax',
                'EMAIL' => 'r.email'
            ))
            ->where('b.mode="rest" and b.number like "R-%" and r.id=' . $restaurant->getId()) //get only billings
            ->order('b.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('ID', array('decorator' => '#{{ID}}'));
        $grid->updateColumn('CID', array('hidden' => 1));
        $grid->updateColumn('EMAIL', array('hidden' => 1));
        $grid->updateColumn('FAX', array('hidden' => 1));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'billstatusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Betrag'), array('callback' => array('function' => 'intToPriceWithNegative', 'params' => array('{{' . __b('Betrag') . '}}'))));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'sqlTimeToDMY', 'params' => array('{{' . __b('Bis') . '}}'))));

        $statis = array(
            '0' => __b('Nicht versand'),
            '1' => __b('Unbezahlt'),
            '2' => __b('Bezahlt'),
            '3' => __b('Teilbezahlt'),
            '' => __b('Alle')
        );

        //add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__b('Status'), array('values' => $statis))
            ->addFilter('ID')
            ->addFilter(__b('Nummer'))
            ->addFilter(__b('Von'))
            ->addFilter(__b('Bis'));

        $grid->addFilters($filters);

        //add header script
        $this->view->headerScript = $grid->getHeaderScript();
        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Set new restaurant if we have master admin set or show infomation
     */
    public function loginredirectAction() {
        $serviceId = $this->getRequest()->getParam('serviceId', null);
        if (is_null($serviceId)) {
            $this->error(__b('Kein Restautrant wurde angegeben'));
            $this->_redirect('/restaurant/');
        }
        $service = new Yourdelivery_Model_Servicetype_Restaurant($this->getRequest()->getParam('serviceId'));

        if (is_object($this->session->masterAdmin)) {
            $this->session->currentRestaurant = $service;
        } else {
            $this->error(__b('Bitte loggen Sie sich über Restaurant Login ein, um die Daten für') . " " . $service->getName() . " zu bearbeiten");
        }

        $this->_redirect('/restaurant/');
    }

    /**
     * Login
     */
    public function loginAction() {

        //redirect if already logged in
        if (is_object($this->session->admin) || is_object($this->session->masterAdmin)) {
            $this->_redirect('/restaurant/');
        }

        //get our request
        $request = $this->getRequest();

        $form = new Yourdelivery_Form_Restaurant_Login();
        $form->populate(array());
        $this->view->form = $form;

        if ($request->isPost()) {
            if (!$form->isValid($request->getPost())) {
                return;
            }

            $user = $form->getValue('user');
            $pass = $form->getValue('pass');
            $restaurantId = $form->getValue('restaurantId');

            if (is_null($user) || is_null($pass) || (strlen(trim($user)) == 0) || (strlen(trim($pass)) == 0)) {
                $this->warn(__b('Keine Zugangsdaten angegeben!'));
            } else if (is_null($restaurantId) || (strlen(trim($restaurantId)) == 0)) {
                $this->warn(__b('Kein Restaurant angegeben!'));
            } else {
                //insert login values into auth adapter
                $this->adminAuth
                    ->setIdentity($user)
                    ->setCredential($pass);
                //get result ...
                $result = $this->adminAuth->authenticate();

                //... and check it
                switch ($result->getCode()) {

                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        $this->warn(__b('Diesen Account gibt es nicht!'));
                        break;

                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->warn(__b('Das Passwort ist falsch!'));
                        break;

                    case Zend_Auth_Result::SUCCESS:
                        try {
                            $customer = new Yourdelivery_Model_Customer(null, $user);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->warn(__b('Benutzer Email ist fehlerhaft!'));
                            break;
                        }

                        try {
                            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->warn(__b('Restaurant Id ist fehlerhaft!'));
                            break;
                        }

                        if (!$customer->isAdmin($restaurant)) {
                            $this->warn(__b('Sie haben keine Admin Rechte für dieses Restaurant!'));
                            break;
                        }

                        //direct redirect to intern page
                        $result = $this->setupLogin($result->getIdentity(), $restaurant);
                        if ($result) {
                            $this->logger->adminInfo(sprintf('Successfully loggged in admin for restaurant %d', $restaurantId));
                            $this->_redirect('/restaurant');
                        } else {
                            $this->warn(__b('Fehler beim einloggen!'));
                            $this->logger->err('Could not log admin in ' . $result->getIdentity());
                        }
                        break;

                    default:
                        $this->warn(__b('Unbekannter Fehler!'));
                }
            }
        }
    }

    /**
     * Set the login session by the admin's email
     *
     * @param string $admin,
     * @param Yourdelivery_Model_Servicetype_Restaurant $restaurant
     * @return boolean
     */
    private function setupLogin($admin, $restaurant) {
        if (is_null($this->session->admin)) {
            $this->session->admin = new Yourdelivery_Model_Customer(null, $admin);
            $this->session->currentRestaurant = $restaurant;
            $this->info(__b('Erfolgreich autentifiziert.'));
            return true;
        } else {
            return true;
        }
    }

    /**
     *  Log out admin
     *
     * @return boolean
     */
    private function setupLogout() {
        if (!is_null($this->session->admin) || !is_null($this->session->masterAdmin)) {
            $this->session->unsetAll();
            $this->info(__b('Sie haben sich erfolgreich abgemeldet'));
            return true;
        } else {
            $this->warn(__b('Fehler beim abmelden'));
            return false;
        }
    }

    /**
     * Logout
     */
    public function logoutAction() {
        $this->setupLogout();
        $this->_redirect("restaurant/login");
    }

    /*
     * show the meanu with edit fields
     */

    public function menuAction() {
        $this->view->assign('jsExtMenu', 'true');
    }

    /*
     * show the meanu om peeview modus
     */

    public function menupreviewAction() {

    }

    public function addsizeAction() {

    }

    public function postDispatch() {
        parent::postDispatch();
        // set right headers and footers
    }

    /**
     * Delete the cache for restaurant
     * @author vpriem
     * @since 06.10.2010
     */
    public function uncacheAction() {
        set_time_limit(0);

        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }

        $restaurant->uncache(true);
        $restaurant->uncacheRanges();

        $this->success(__b('Der Cache wurde erfolgreich gelöscht'));
        $this->_redirect("/restaurant");
    }

    /*
     * show notepad for the restaurant
     * @author vpriem
     * @since 06.10.2010
     */

    public function notepadAction() {
        $restaurant = $this->initRestaurant();
        if (is_null($restaurant->getId())) {
            $this->_redirect('/index');
        }


        $this->view->assign('restaurant', $restaurant);
    }

}
