<?php

/**
 * Administration
 * @author vpriem
 * @since 30.11.2010
 */
class AdministrationController extends Default_Controller_AdministrationBase {

    /**
     * Dashboard alias
     * @author vpriem
     * @since 10.11.2010
     */
    public function indexAction() {
        $this->_redirect('/administration/dashboard');
    }

    /**
     * Dashboard
     * @author alex
     * @since 08.12.2010
     */
    public function dashboardAction() {

    }

    public function supportAction() {
        $this->view->assign('navsupport', 'active');
        $this->view->numbers = Yourdelivery_Model_Support::all();
        /* @deprecated BLACKLIST */
        $filename = BLACKLIST;
        if (file_exists($filename)) {
            $this->view->blacklist = file_get_contents($filename);
        }
    }

    /**
     * show information about everything. Implemented in Request/AdministrationController
     */
    public function detailedinfoAction() {
        $this->view->assign('navindex', 'active');
        $this->view->numbers = Yourdelivery_Model_Support::all();
        /* @deprecated BLACKLIST */
        $filename = BLACKLIST;
        if (file_exists($filename)) {
            $this->view->blacklist = file_get_contents($filename);
        }
    }

    /**
     * show a sortable, filterable table of all users
     */
    public function usersAction() {
        
        $showdeleted = $this->session->showdeletedusers;

        // build select
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
            ->from(array('c' => 'customers'), array(
                __b('ID') => 'id',
                __b('Registriert') => 'created',
                __b('Geändert') => 'updated',
                __b('Vorname') => 'prename',
                __b('Nachname') => 'name',
                __b('eMail') => 'email',
                __b('Tel') => 'tel',
                __b('Gelöscht') => 'deleted',
                __b('Newsletter') => 'email',
                'socialNetwork' => new Zend_Db_Expr("IF(c.facebookId > 0, 'Facebook', '-')"),
            ))
            ->joinLeft(array('cc' => 'customer_company'), 'cc.customerId = c.id', array(
                __b('RID') => 'cc.id'))
            ->joinLeft(array('co' => 'companys'), 'cc.companyId = co.id', array(
                __b('Firma') => 'name',
                __b('FirmaId') => 'co.id',
            ))
            ->where('c.deleted / c.id < ' . ($showdeleted + 1))
            ->order('c.id DESC');

        //update some columns
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array('csv'));
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('RID'), array('hidden' => 1));
        $grid->updateColumn(__b('FirmaId'), array('hidden' => 1));
        $grid->updateColumn('eMail', array('title' => __b('eMail'), 'callback' => array('function' => 'emailinfo', 'params' => array('{{eMail}}'))));
        $grid->updateColumn(__b('Firma'), array('decorator' => '<a href="/administration_company_edit/index/companyid/{{'.__b('FirmaId').'}}">{{'.__b('Firma').'}}</a>'));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{'.__b('Registriert').'}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{'.__b('Geändert').'}}'))));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{'.__b('ID').'}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Newsletter'), array('callback' => array('function' => 'Default_Helpers_Grid_Customer::checkNewsletter', 'params' => array('{{'.__b('Newsletter').'}}'))));
        $grid->updateColumn('socialNetwork', array('title' => __b("Sozialnetzwerk")));

        if ($showdeleted) {
            $grid->updateColumn(__b('Gelöscht'), array('callback' => array('function' => 'deletedUserToReadable', 'params' => array('{{' . __b('Gelöscht') . '}}'))));
        } else {
            $grid->updateColumn(__b('Gelöscht'), array('hidden' => 1));
        }
        $grid->updateColumn(__b('Tel'), array('decorator' => '<small><a href="sip:{{' . __b('Tel') . '}}" class="yd-sip">{{' . __b('Tel') . '}}</a></small>'));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Gelöscht'), array('values' => array(
                    '0' => __b('aktiv'),
                    '1' => __b('gelöscht'),
                )))
                ->addFilter(__b('Vorname'))
                ->addFilter(__b('Nachname'))
                ->addFilter(__b('eMail'))
                ->addFilter(__b('Tel'))
                ->addFilter('anewsletter', array('values' => array(
                    '1' => __b('Ja'),
                    '0' => __b('Nein'),
                    '' => __b('Alle'),
                )))
                ->addFilter('socialNetwork', array('values' => array(
                    '-' => __b("Keins"),
                    'Facebook' => "Facebook",
                )))
                ->addFilter(__b('Firma'));
        $grid->addFilters($filters);
        
        // add options
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<a href=\"/administration_user_edit/index/userid/{{" . __b('ID') . "}}\">" . __b('Info') . "</a>
                <a href=\"/administration/userlogin/id/{{" . __b('ID') . "}}\" target=\"_blank\">" . __b('Login') . "</a>
                <a href=\"/administration_user/delete/id/{{" . __b('ID') . "}}\" onclick=\"javascript:return confirm('" . __b('Vorsicht!! Soll dieser User wirklich gelöscht werden?') . "')\">" . __b('Löschen') . "</a>"
        );
        $grid->addExtraColumns($option);

        // add header script
        $this->view->headerScript = $grid->getHeaderScript();
        $this->view->showdeleted = $showdeleted;
        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }
    
    public function placarAction(){
        $showdeleted = $this->session->showdeletedcomp;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('c' => 'orders'), array(
                    'total' => 'count(id)',
                ))
                ->where('state = 2 AND time >= "2013-01-01"');
        
        $rows = $db->query($select);
        foreach ($rows as $row){
            $this->view->b = $row['total']-1;
        }
        $days = floor((strtotime("31-May-2013")-strtotime("01-Jan-2013"))/86400);
        $untilNow = floor((time()-strtotime("01-Jan-2013"))/86400);
        $this->view->c = floor(50000 - ($untilNow*50000)/$days);
    }

    /**
     * show a sortable, filterable table of all companys
     */
    public function companysAction() {
        $this->view->assign('navcompanys', 'active');

        $showdeleted = $this->session->showdeletedcomp;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('c' => 'companys'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'created',
                    __b('Geändert') => 'updated',
                    __b('Name') => 'name',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (c.street," ",c.hausnr," ",c.plz)'),
                    __b('Kundennummer') => 'customerNr',
                    __b('Gelöscht') => 'deleted',
                    __b('Status') => 'status'
                ))
                ->joinLeft(array('ct' => 'city'), 'c.cityId = ct.id', array(
                    __b('Stadt') => 'ct.city')
                )
                // if $showdeleted = 0, only active entries will be listed, if $showdeleted = 1, the deleted will be listed too
                ->where('c.deleted < ?', ($showdeleted + 1))
                ->order('c.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setPagination(20);
        $grid->setExport(array());
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('Aktiv'), array('callback' => array('function' => 'deletedToReadable', 'params' => array('{{' . __b('Aktiv') . '}}'))));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Registriert') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'companyStatusToReaddable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        if ($showdeleted) {
            $grid->updateColumn(__b('Gelöscht'), array('callback' => array('function' => 'deletedToReadable', 'params' => array('{{' . __b('Gelöscht') . '}}'))));
        } else {
            $grid->updateColumn(__b('Gelöscht'), array('hidden' => 1));
        }

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        // stati
        $deletedStates = array(
            '0' => __b('nicht gelöscht'),
            '1' => __b('gelöscht'),
            '' => __b('Alle')
        );

        $activeStates = array(
            '1' => __b('aktiviert'),
            '0' => __b('deaktiviert'),
            '' => __b('Alle')
        );

        // add filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__b('ID'))
                ->addFilter(__b('Gelöscht'), array('values' => $deletedStates))
                ->addFilter(__b('Status'), array('values' => $activeStates))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Adresse'))
                ->addFilter(__b('Kundennummer'))
                ->addFilter(__b('Stadt'));
        $grid->addFilters($filters);

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->callback(array('function' => 'optionsForCompanies', 'params' => array('{{' . __b('ID') . '}}')));
        //add extra rows
        $grid->addExtraColumns($option);

        $this->view->showdeleted = $showdeleted;
        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all restaurants
     * @param
     * @return
     */
    public function servicesAction() {
        $this->view->assign('navservices', 'active');

        $showdeleted = $this->session->showdeletedservices;
        $showoffline = $this->session->showofflineservices;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('r' => 'restaurants'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'r.created',
                    __b('Geändert') => 'r.updated',
                    __b('Name') => 'r.name',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (r.street, " ", r.hausnr)'),
                    __b('PLZ') => 'r.plz',
                    __b('Kundennummer') => 'r.customerNr',
                    __b('Status') => 'r.isOnline',
                    __b('Offline Status') => 'r.status',
                    __b('Gelöscht') => 'r.deleted',
                    __b('Geprüft') => 'r.checked',
                    'isLogo' => 'r.isLogo',
                    'onlyCash' => 'r.onlycash',
                    'r.laxContract',
                    __b('Versand') => 'r.notify',
                    'Franchise' => 'r.franchiseTypeId',
                    __b('Stadt') => 'COALESCE(CONCAT(pct.city," (",ct.city,")"), ct.city)',
                    __b('Bewertung') => new Zend_Db_Expr('(r.ratingQuality + r.ratingDelivery) / 2')
                ))
                ->joinLeft(array('ct' => 'city'), 'r.cityId = ct.id', array())
                ->joinLeft(array('pct' => 'city'), 'ct.parentCityId = pct.id', array())
                /*
                 * if $showdeleted = 0, only active entries will be listed, if $showdeleted = 1, the deleted will be listed too
                 * adding 1, so the expression always has a value, even if the variable $showdeleted is null
                 * if $showoffline = 0, only online entries will be listed, if $showoffline = 1, the offline will be listed too
                 * if we want to show ofline, compare to the count of elements in ofline states array
                 */
                ->where('r.deleted < ' . ($showdeleted + 1) . ' AND r.isOnline >= ' . intval(!$showoffline))
                ->group('r.id')
                ->order('r.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array('csv'));
        $grid->export = array('pdf', 'csv');
        $grid->setPagination(20);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Registriert') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Offline Status'), array('searchType' => "="));
        $grid->updateColumn('Franchise', array('searchType' => 'equal', 'title' => __b('Franchise'), 'callback' => array('function' => 'getFranchise', 'params' => array('{{Franchise}}'))));
        $grid->updateColumn(__b('Geprüft'), array('callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('Geprüft') . '}}'))));
        $grid->updateColumn(__b('Wünscht webseite'), array('callback' => array('function' => 'intToYesNoNullIcon', 'params' => array('{{' . __b('Wünscht webseite') . '}}'))));
        $grid->updateColumn(__b('Wünscht sms-drucker'), array('callback' => array('function' => 'intToYesNoNullIcon', 'params' => array('{{' . __b('Wünscht sms-drucker') . '}}'))));
        $grid->updateColumn(__b('Druckbeteiligung'), array('callback' => array('function' => 'intToYesNoNullIcon', 'params' => array('{{' . __b('Druckbeteiligung') . '}}'))));
        $grid->updateColumn('isLogo', array('title' => __b('Bild ist Logo'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{isLogo}}'))));
        $grid->updateColumn('laxContract', array('title' => __b('Kann jederzeit kündigen'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{laxContract}}'))));
        $grid->updateColumn('onlyCash', array('title' => __b('Nur Barzahlung'), 'callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{onlyCash}}'))));
        $grid->updateColumn(__b('Bewertung'), array('callback' => array('function' => 'ratingsToImg', 'params' => array('{{' . __b('Bewertung') . '}}', '{{' . __b('ID') . '}}'))));

        if ($showoffline) {
            $grid->updateColumn(__b('Offline Status'), array('callback' => array('function' => 'offlineStatusToReadable', 'params' => array('{{' . __b('Offline Status') . '}}'))));
            $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        } else {
            $grid->updateColumn(__b('Offline Status'), array('hidden' => 1));
            $grid->updateColumn(__b('Status'), array('hidden' => 1));
        }

        if ($showdeleted) {
            $grid->updateColumn(__b('Gelöscht'), array('callback' => array('function' => 'deletedToReadable', 'params' => array('{{' . __b('Gelöscht') . '}}'))));
        } else {
            $grid->updateColumn(__b('Gelöscht'), array('hidden' => 1));
        }

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        // add filters
        $filters = new Bvb_Grid_Filters();
        $activeStates = array(
            '1' => __b('online'),
            '0' => __b('offline'),
            '' => __b('Alle')
        );

        $deletedStates = array(
            '0' => __b('nicht gelöscht'),
            '1' => __b('gelöscht'),
            '' => __b('Alle')
        );

        $yesNoStates = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle'));

        $yesNoNullStates = array(
            '0' => __b('Keine Info'),
            '1' => __b('Ja'),
            '2' => __b('Nein')
        );

        $notifyValues = array(
            'fax' => __b('Faxversand'),
            'acom' => __b('Acom Schnittstelle'),
            'all' => __b('Fax und eMail'),
            'email' => __b('eMail'),
            'sms' => __b('GPRS Drucker'),
            'smsemail' => __b('GPRS Drucker und eMail'),
            'dominos' => __b('Dominos Schnittstelle'),
            'ecletica' => __b('Ecletica Schnittstelle'),
            'phone' => "Telefone"
        );

        $franchises = Yourdelivery_Model_Servicetype_Franchise::all();
        $franchiseType = array('' => __b('Alle'));
        foreach ($franchises as $franchise) {
            $franchiseType[$franchise['id']] = __b($franchise['name']);
        }

        $offlineStates = Yourdelivery_Model_Servicetype_Abstract::getStati();
        $offlineStates[''] = __b('Alle');

        // add filters
        $filters->addFilter(__b('ID'))
                ->addFilter('Franchise', array('values' => $franchiseType))
                ->addFilter(__b('Geprüft'), array('values' => $yesNoStates))
                ->addFilter('isLogo', array('values' => $yesNoStates))
                ->addFilter('onlyCash', array('values' => $yesNoStates))
                ->addFilter('laxContract', array('values' => $yesNoStates))
                ->addFilter(__b('Wünscht webseite'), array('values' => $yesNoNullStates))
                ->addFilter(__b('Wünscht sms-drucker'), array('values' => $yesNoNullStates))
                ->addFilter(__b('Druckbeteiligung'), array('values' => $yesNoNullStates))
                ->addFilter(__b('Name'))
                ->addFilter(__b('Adresse'))
                ->addFilter(__b('PLZ'))
                ->addFilter(__b('Kundennummer'))
                ->addFilter(__b('Stadt'))
                ->addFilter(__b('Versand'), array('values' => $notifyValues));

        if ($showoffline) {
            $filters->addFilter(__b('Offline Status'), array('values' => $offlineStates));
            $filters->addFilter(__b('Status'), array('values' => $activeStates));
        }

        if ($showdeleted) {
            $filters->addFilter(__b('Gelöscht'), array('values' => $deletedStates));
        }

        $grid->addFilters($filters);

        // add extra rows
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Options'))
                ->decorator(
                        '<a href="/administration_service_edit/index/id/{{ID}}">'.__b('Info').'</a>
                 <a href="/administration/servicelogin/id/{{ID}}" target="_blank">'.__b('Backend').'</a>
                 <a href="/administration/partnerlogin/id/{{ID}}" target="_blank">'.__b('Partner').'</a>
                 <a href="/administration_service/delete/id/{{ID}}" onclick="javascript:return confirm(\''.__b("Vorsicht!! Soll dieser Dienstleister wirklich gelöscht werden?").'\')">'.__b('Löschen').'</a>'
        );
        $grid->addExtraColumns($option);

        $this->view->showdeleted = $this->session->showdeletedservices;
        $this->view->showoffline = $this->session->showofflineservices;

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }
    
    /**
     * show a sortable, filterable table of all restaurants
     * @param
     * @return
     */
    public function servicesallAction() {
        $this->view->assign('navservices', 'active');

        $showdeleted = $this->session->showdeletedservices;
        $showoffline = $this->session->showofflineservices;

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('r' => 'restaurants'), array(
                    'ID' => 'id',
                    'Nome' => 'r.name',
                    'Endereco' => new Zend_Db_Expr('CONCAT (r.street, " ", r.hausnr)'),
                    'Bairro' => 'cv.neighbour',
                    'Cidade' => 'cv.city',
                    'Estado' => 'ct.state',
                    'CEP' => 'r.plz',
                    'CNPJ' => 'r.ustIdNr',
                    'Telefone' => 'r.tel',
                    'Abre DOM' => 'ro0.from',
                    'Fecha DOM' => 'ro0.until',
                    'Abre SEG' => 'ro1.from',
                    'Fecha SEG' => 'ro1.until',
                    'Abre TER' => 'ro2.from',
                    'Fecha TER' => 'ro2.until',
                    'Abre QUA' => 'ro3.from',
                    'Fecha QUA' => 'ro3.until',
                    'Abre QUI' => 'ro4.from',
                    'Fecha QUI' => 'ro4.until',
                    'Abre SEX' => 'ro5.from',
                    'Fecha SEX' => 'ro5.until',
                    'Abre SAB' => 'ro6.from',
                    'Fecha SAB' => 'ro6.until',
                    'Abre FER' => 'ro10.from',
                    'Fecha FER' => 'ro10.until',
                    'URL' => new Zend_Db_Expr('CONCAT ("http://www.janamesa.com.br/", r.restUrl)'),'',
                    'Franchise' => 'r.franchiseTypeId',
                    __b('Offline Status') => 'r.status',
                    'email' => 'r.email'
                ))
                ->joinLeft(array('ct' => 'city'), 'r.cityId = ct.id', array())
                ->joinLeft(array('cv' => 'city_verbose'), 'cv.cityId = ct.id', array())
                ->joinLeft(array('ro0' => 'restaurant_openings'), 'ro0.restaurantId = r.id AND ro0.day = 0', array())
                ->joinLeft(array('ro1' => 'restaurant_openings'), 'ro1.restaurantId = r.id AND ro1.day = 1', array())
                ->joinLeft(array('ro2' => 'restaurant_openings'), 'ro2.restaurantId = r.id AND ro2.day = 2', array())
                ->joinLeft(array('ro3' => 'restaurant_openings'), 'ro3.restaurantId = r.id AND ro3.day = 3', array())
                ->joinLeft(array('ro4' => 'restaurant_openings'), 'ro4.restaurantId = r.id AND ro4.day = 4', array())
                ->joinLeft(array('ro5' => 'restaurant_openings'), 'ro5.restaurantId = r.id AND ro5.day = 5', array())
                ->joinLeft(array('ro6' => 'restaurant_openings'), 'ro6.restaurantId = r.id AND ro6.day = 6', array())
                ->joinLeft(array('ro10' => 'restaurant_openings'), 'ro10.restaurantId = r.id AND ro10.day = 10', array())
                ->where('r.deleted = 0 ')
                ->group('r.id')
                ->order('r.id');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setcharEncoding("ISO-8859-1");
        $grid->setExport(array('excel'));
//        $grid->export = array('pdf', 'xml');
        $grid->setPagination(20);

        // update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('Offline Status'), array('searchType' => "="));
        
        $grid->updateColumn(__b('Offline Status'), array('callback' => array('function' => 'offlineStatusToReadable', 'params' => array('{{' . __b('Offline Status') . '}}'))));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'statusToReadable', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn('Franchise', array('searchType' => 'equal', 'title' => __b('Franchise'), 'callback' => array('function' => 'getFranchise', 'params' => array('{{Franchise}}'))));

        $offlineStates = Yourdelivery_Model_Servicetype_Abstract::getStati();
        $offlineStates[''] = __b('Alle');
        
        $activeStates = array(
            '1' => __b('online'),
            '0' => __b('offline'),
            '' => __b('Alle')
        );

        $franchises = Yourdelivery_Model_Servicetype_Franchise::all();
        $franchiseType = array('' => __b('Alle'));
        foreach ($franchises as $franchise) {
            $franchiseType[$franchise['id']] = __b($franchise['name']);
        }


        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all contacts
     */
    public function contactsAction() {
        $this->view->assign('navcontacts', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);

        //set path so the sorting and filtering will stay when we edit some entry
        $path = $this->getRequest()->getPathInfo();
        $this->session->contactspath = $path;

        //select orders
        $select = $db->select()->from(array('c' => 'contacts'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'created',
                    __b('Geändert') => 'updated',
                    __b('Vorname') => 'prename',
                    __b('Nachname') => 'name',
                    __b('eMail') => 'email',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (c.street," ",c.hausnr, ", " , c.plz)'),
                    __b('Tel') => 'tel',
                    __b('Fax') => 'fax',
                    __b('Zuordnungen') => 'id'
                ))
                ->order('c.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('eMail'), array('decorator' => '<a href="mailto:{{' . __b('eMail') . '}}">{{' . __b('eMail') . '}}</a>'));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Registriert') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn(__b('Zuordnungen'), array('callback' => array('function' => 'getAssociations', 'params' => array('{{' . __b('ID') . '}}'))));
        $grid->updateColumn(__b('Tel'), array('decorator' => '<small><a href="sip:{{' . __b('Tel') . '}}" class="yd-sip">{{' . __b('Tel') . '}}</a></small>'));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        //add filters
        $filters = new Bvb_Grid_Filters();

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Vorname'))
                ->addFilter(__b('Nachname'))
                ->addFilter(__b('eMail'))
                ->addFilter(__b('Tel'))
                ->addFilter(__b('Adresse'))
                ->addFilter(__b('Tel'))
                ->addFilter(__b('Fax'));

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<a href=\"/administration_contact/edit/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a>
                     <a href=\"/administration_contact/delete/id/{{" . __b('ID') . "}}\" onclick=\"javascript:return confirm('" . __b("Vorsicht!! Soll dieser Kontakt wirklich gelöscht werden?") . "')\">" . __b('Löschen') . "</a>"
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all contacts without any associations to firms or restaurants
     */
    public function contactsunusedAction() {
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);

        //set path so the sorting and filtering will stay when we delete some entry
        $path = $this->getRequest()->getPathInfo();
        $this->session->contactsWithoutAssociationspath = $path;

        //select orders
        $select = $db->select()->from(array('c' => 'contacts'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'created',
                    __b('Geändert') => 'updated',
                    __b('Vorname') => 'prename',
                    __b('Nachname') => 'name',
                    __b('eMail') => 'email',
                    __b('Adresse') => new Zend_Db_Expr('CONCAT (c.street," ",c.hausnr, ", " , c.plz)'),
                    __b('Tel') => 'tel',
                    __b('Fax') => 'fax',
                ))
                ->joinLeft(array('comp' => 'companys'), 'comp.contactId=c.id', array(__b('Firma') => 'comp.name', __b('FirmaId') => 'comp.id'))
                ->joinLeft(array('rest' => 'restaurants'), 'rest.contactId=c.id', array(__b('Restaurant') => 'rest.name', __b('RestaurantId') => 'rest.id'))
                ->where('comp.id is NULL and rest.id IS NULL')
                ->order('c.name');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('eMail'), array('decorator' => '<a href="mailto:{{' . __b('eMail') . '}}">{{' . __b('eMail') . '}}</a>'));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Registriert') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn(__b('Firma'), array('hidden' => 1));
        $grid->updateColumn(__b('FirmaId'), array('hidden' => 1));
        $grid->updateColumn(__b('Restaurant'), array('hidden' => 1));
        $grid->updateColumn(__b('RestaurantId'), array('hidden' => 1));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        //add filters
        $filters = new Bvb_Grid_Filters();

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Vorname'))
                ->addFilter(__b('Nachname'))
                ->addFilter(__b('eMail'))
                ->addFilter(__b('Tel'))
                ->addFilter(__b('Adresse'))
                ->addFilter(__b('Tel'))
                ->addFilter(__b('Fax'));

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<a href=\"/administration_contact/edit/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a>
                     <a href=\"/administration_contact/delete/id/{{" . __b('ID') . "}}\" onclick=\"javascript:return confirm('" . __b("Vorsicht!! Soll dieser Kontakt wirklich gelöscht werden?") . "')\">" . __b('Löschen') . "</a>"
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all discounts
     */
    public function discountsAction() {
        $this->view->assign('navdiscounts', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('r' => 'rabatt'), array(
                    __b('ID') => 'id',
                    __b('Erstellt') => 'created',
                    __b('Geändert') => 'updated',
                    __b('Typ') => 'type',
                    __b('Referer') => 'referer',
                    __b('Name') => 'name',
                    __b('Info') => 'info',
                    __b('Wiederholend') => 'rrepeat',
                    __b('Anzahl') => 'countUsage',
                    'From' => 'start',
                    'Until' => 'end',
                    __b('Status') => 'status',
                    __b('Höhe') => 'rabatt',
                    __b('Art') => 'kind',
                    __b('TypeClear') => 'type',
                    __b('Optionen') => 'type'
                ))
                ->order('r.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('Status'), array('callback' => array('function' => 'intToStatusDiscount', 'params' => array('{{' . __b('Status') . '}}'))));
        $grid->updateColumn(__b('Wiederholend'), array('callback' => array('function' => 'rrepeatToReadable', 'params' => array('{{' . __b('Wiederholend') . '}}', '{{' . __b('Anzahl') . '}}'))));
        $grid->updateColumn(__b('Anzahl'), array('hidden' => 1));
        $grid->updateColumn(__b('Höhe'), array('callback' => array('function' => 'rabattToReadable', 'params' => array('{{' . __b('Höhe') . '}}', '{{' . __b('Art') . '}}'))));
        $grid->updateColumn(__b('Art'), array('callback' => array('function' => 'rabattKindToReadable', 'params' => array('{{' . __b('Art') . '}}'))));
        $grid->updateColumn(__b('Referer'), array('callback' => array('function' => 'hideRefererTypeOne', 'params' => array('{{' . __b('TypeClear') . '}}', '{{' . __b('Referer') . '}}'))));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Erstellt'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Erstellt') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn('From', array('title' => __b('Von'), 'callback' => array('function' => 'dateFull', 'params' => array('{{From}}'))));
        $grid->updateColumn('Until', array('title' => __b('Bis'), 'callback' => array('function' => 'dateFull', 'params' => array('{{Until}}'))));
        $grid->updateColumn(__b('Typ'), array('callback' => array('function' => 'getDiscountType', 'params' => array('{{' . __b('Typ') . '}}'))));
        $grid->updateColumn(__b('Optionen'), array('callback' => array('function' => 'getDiscountOptions', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('Optionen') . '}}'))));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Erstellt'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        //add filters
        $filters = new Bvb_Grid_Filters();

        //translate discount type
        $allTypes = Yourdelivery_Model_Rabatt::getDiscountTypes();
        $types = array();
        foreach ($allTypes as $typeId => $value) {
            $types[$typeId] = $value['name'];
        }
        $types[''] = __b('Alle');

        //translate stati
        $states = array(
            '0' => __b('Deaktiviert'),
            '1' => __b('Aktiviert'),
            '' => __b('Alle')
        );

        $repeatStates = array(
            '0' => __b('Einmalig'),
            '1' => __b('Wiederholt'),
            '2' => __b('Anzahl'),
            '' => __b('Alle')
        );

        $kinds = array(
            '0' => '%',
            '1' => __b('€'),
            '' => __b('Alle')
        );

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Status'), array('values' => $states))
                ->addFilter(__b('Wiederholend'), array('values' => $repeatStates))
                ->addFilter(__b('Art'), array('values' => $kinds))
                ->addFilter(__b('Typ'), array('values' => $types))
                ->addFilter('From')
                ->addFilter('Until')
                ->addFilter(__b('Name'))
                ->addFilter(__b('Referer'))
                ->addFilter(__b('Info'))
                ->addFilter(__b('Höhe'));

        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Show a sortable, filterable table of all couriers
     * @author vpriem
     */
    public function couriersAction() {
        $this->view->assign('navcouriers', 'active');

        // create grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select(
                        Yourdelivery_Model_Courier::getGrid()
        ));
        $grid->updateColumn(ID, array('searchType' => '='));
        $grid->updateColumn('eMail', array('decorator' => '<a href="mailto:{{eMail}}">{{eMail}}</a>'));
        $grid->updateColumn('Registriert', array('title' => __b('Registriert'), 'callback' => array('function' => 'dateFull', 'params' => array('{{Registriert}}'))));
        $grid->updateColumn('Geändert', array('title' => __b('Geändert'), 'callback' => array('function' => 'dateFull', 'params' => array('{{Geändert}}'))))
                ->updateColumn('Adresse', array('title' => __b('Adresse')))
                ->updateColumn('Name', array('title' => __b('Name')));
        if (!$this->session->show_edit_time) {
            $grid->updateColumn('Registriert', array('hidden' => 1));
            $grid->updateColumn('Geändert', array('hidden' => 1));
        }

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Optionen'))
                ->decorator(
                        '<a href="/administration_courier/edit/cid/{{ID}}">' . __b('Editieren') . '</a>
                 <a href="/administration_courier/delete/cid/{{ID}}" class="yd-are-you-sure">' . __b('Löschen') . '</a>'
        );
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all sales persons
     */
    public function salespersonsAction() {
        $this->view->assign('navsalespersons', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('s' => 'salespersons'), array(
                    __b('ID') => 'id',
                    __b('Registriert') => 'created',
                    __b('Geändert') => 'updated',
                    __b('Name') => new Zend_Db_Expr('CONCAT (s.prename," ",s.name)'),
                    __b('Email') => 'email',
                    'Callcenter' => 'callcenter',
                    __b('Anmerkung') => 'description'
                ))
                ->joinLeft(array('admins' => 'admin_access_users'), 'admins.email=s.email', array('Admin' => new Zend_Db_Expr('admins.email is null')))
                ->order('s.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Registriert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Registriert') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));
        $grid->updateColumn('Callcenter', array(
            'title' => __b('Call Center/Aussendienst'),
            'callback' => array('function' => 'callcenterToReadable', 'params' => array('{{Callcenter}}')
                )));
        $grid->updateColumn('Admin', array(
            'title' => __b('Zugang zum Admin-Backend'),
            'callback' => array('function' => 'registeredAsAdmin', 'params' => array('{{Admin}}')
                )));
        $grid->updateColumn(__b('Email'), array('decorator' => '<a href="mailto:{{' . __b('Email') . '}}">{{' . __b('Email') . '}}</a>'));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Registriert'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        //add filters
        $filters = new Bvb_Grid_Filters();

        $callcenter = array(
            '0' => __b('Aussendienst'),
            '1' => __b('Call Center'),
            '' => __b('Alle')
        );

        //add filters
        $filters->addFilter('ID')
                ->addFilter('Name')
                ->addFilter('Callcenter', array('values' => $callcenter))
                ->addFilter('Anmerkung');

        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();

        $option->position('right')->name(__b('Options'))->decorator(
                "<div>
                        <a href=\"/administration_salesperson/edit/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a>
                        <a href=\"/administration_salesperson/info/id/{{" . __b('ID') . "}}\">" . __b('Info') . "</a>
                    </div>"
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all users with administration rights
     */
    public function adminsAction() {
        $this->view->assign('navadmin', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('a' => 'admin_access_users'), array(
                    __b('ID') => 'id',
                    __b('Name') => 'name',
                    __b('eMail') => 'email',
                    __b('Gruppen') => 'id',
                ))
                ->order('a.id DESC');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $groupfilter = array();
        foreach (Yourdelivery_Model_DbTable_Admin_Access_Groups::getAllGroups() as $g) {
            $groupfilter["*" . $g['id'] . "*"] = $g['name'];
        }
        $groupfilter[''] = __b('Alle');

        //add filters
        $filters = new Bvb_Grid_Filters();

        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Name'))
                ->addFilter(__b('eMail'));
        $grid->addFilters($filters);

        //update some columns
        $grid->updateColumn(__b('Gruppen'), array('callback' => array('function' => 'adminGroupLinks', 'params' => array('{{' . __b('Gruppen') . '}}'))));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<div>
                        <a href=\"/administration_adminrights/edit/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a><br />
                    </div>"
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all users with administration groups
     */
    public function admingroupsAction() {
        $this->view->assign('navadmin', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('g' => 'admin_access_groups'), array(
                    __b('ID') => 'id',
                    __b('Name') => 'name',
                ))
                ->where('g.name != "Admin"')
                ->order('g.id ASC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<div>
                        <a href=\"/administration_adminrights/editgroup/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a><br />
                    </div>"
        );
        //add extra rows
        $grid->addExtraColumns($option);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all additional billings
     */
    public function billingassetsAction() {
        //set path so the sorting and filtering will stay when we change a billing asset state
        $path = $this->getRequest()->getPathInfo();

        $this->view->assign('navbilling', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        #$grid->export = array('pdf','csv');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()->from(array('ba' => 'billing_assets'), array(
                    __b('ID') => 'ba.id',
                    __b('Erstellt') => 'ba.created',
                    __b('Geändert') => 'ba.updated',
                    'total' => 'ba.total',
                    __b('Mwst') => 'ba.mwst',
                    __b('Provision') => 'ba.fee',
                    __b('Von') => 'ba.timeFrom',
                    __b('Bis') => 'ba.timeUntil',
                    __b('Beschreibung') => 'ba.description',
                    'ba.billRest',
                    'ba.billCompany',
                    'ba.billCourier',
                ))
                ->joinLeft(array('comp' => 'companys'), 'ba.companyId=comp.id', array(
                    __b('Firma') => 'comp.name',
                    __b('FirmaId') => 'comp.id'
                ))
                ->joinLeft(array('rest' => 'restaurants'), 'ba.restaurantId=rest.id', array(
                    __b('Restaurant') => 'rest.name',
                    __b('RestaurantId') => 'rest.id'
                ))
                ->joinLeft(array('cour' => 'courier'), 'ba.courierId=cour.id', array(
                    __b('Kurier') => 'cour.name',
                    __b('KurierId') => 'cour.id'
                ))
                ->order('ba.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('FirmaId'), array('hidden' => 1));
        $grid->updateColumn(__b('RestaurantId'), array('hidden' => 1));
        $grid->updateColumn(__b('KurierId'), array('hidden' => 1));
        $grid->updateColumn(__b('RechnungId'), array('hidden' => 1));
        $grid->updateColumn('billRest', array('hidden' => 1));
        $grid->updateColumn('billCompany', array('hidden' => 1));
        $grid->updateColumn('billCourier', array('hidden' => 1));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn('total', array('title' => __b('Betrag (Netto)'), 'callback' => array('function' => 'intToPriceWithNegative', 'params' => array('{{total}}'))));
        $grid->updateColumn(__b('Bis'), array('callback' => array('function' => 'dateYMD', 'params' => array('{{' . __b('Bis') . '}}'))));
        $grid->updateColumn(__b('Von'), array('callback' => array('function' => 'dateYMD', 'params' => array('{{' . __b('Von') . '}}'))));
        $grid->updateColumn(__b('Restaurant'), array('decorator' => '<a href="/administration_service_edit/index/id/{{' . __b('RestaurantId') . '}}">{{' . __b('Restaurant') . '}}</a>'));
        $grid->updateColumn(__b('Firma'), array('decorator' => '<a href="/administration_company_edit/index/companyid/{{' . __b('FirmaId') . '}}">{{' . __b('Firma') . '}}</a>'));
        $grid->updateColumn(__b('Kurier'), array('decorator' => '<a href="/administration_courier/edit/id/{{' . __b('KurierId') . '}}">{{' . __b('Kurier') . '}}</a>'));
        $grid->updateColumn(__b('Mwst'), array('decorator' => '{{' . __b('Mwst') . '}} %', 'searchType' => 'equal'));
        $grid->updateColumn(__b('Provision'), array('decorator' => '{{' . __b('Provision') . '}} %'));
        $grid->updateColumn(__b('Erstellt'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Erstellt') . '}}'))));
        $grid->updateColumn(__b('Geändert'), array('callback' => array('function' => 'dateFull', 'params' => array('{{' . __b('Geändert') . '}}'))));

        if (!$this->session->show_edit_time) {
            $grid->updateColumn(__b('Erstellt'), array('hidden' => 1));
            $grid->updateColumn(__b('Geändert'), array('hidden' => 1));
        }

        $filters = new Bvb_Grid_Filters();

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter('total')
                ->addFilter(__b('Von'))
                ->addFilter(__b('Bis'))
                ->addFilter(__b('Provision'))
                ->addFilter(__b('Beschreibung'))
                ->addFilter(__b('Firma'))
                ->addFilter(__b('Restaurant'))
                ->addFilter(__b('Kurier'));
        $grid->addFilters($filters);

        //option row
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')->name(__b('Options'))->decorator(
                "<div>
                        <a href=\"/administration_billingasset/edit/id/{{" . __b('ID') . "}}\">" . __b('Editieren') . "</a><br />
                        <a href=\"/administration_billingasset/delete/id/{{" . __b('ID') . "}}\" onclick=\"javascript:return confirm('" . __b('Vorsicht!! Soll dieser Rechnungposten wirklich gelöscht werden?') . "\">" . __b('Löschen') . "</a>
                    </div>"
        );

        $billings = new Bvb_Grid_Extra_Column();
        $billings->position('right')
                ->name(__b('Rechnungen'))
                ->callback(array('function' => 'billingsForBillingAssets', 'params' => array('{{billRest}}', '{{billCompany}}', '{{billCourier}}')));

        //add extra rows
        $grid->addExtraColumns($option, $billings);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * show a sortable, filterable table of all send emails
     */
    public function emailsAction() {
        $this->view->assign('navemails', 'active');

        // build select
        $db = Zend_Registry::get('dbAdapter');
        $select = $db
                ->select()
                ->from(array('m' => 'emails'), array(
                    __b('ID') => 'id',
                    __b('Typ') => 'type',
                    __b('An') => 'email',
                    __b('Status') => 'status',
                    __b('Am') => new Zend_Db_Expr("DATE_FORMAT(m.time, '%d.%m.%Y %H:%i')"),
                ))
                ->order('m.id DESC');

        // build grid
        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn(__b('ID'), array('decorator' => '#{{' . __b('ID') . '}}'));
        $grid->updateColumn(__b('ID'), array('searchType' => '='));
        $grid->updateColumn(__b('Inhalt'), array('hidden' => 1));
        $grid->updateColumn(__b('An'), array('decorator' => '<a href="mailto:{{' . __b('An') . '}}">{{' . __b('An') . '}}</a>'));

        // set filters
        $filters = new Bvb_Grid_Filters();
        $filters
                ->addFilter(__b('ID'))
                ->addFilter(__b('Typ'), array('values' => array(
                        __b('system') => __b('system'),
                        __b('customer') => __b('customer'),
                        '' => __b('Alle')
                        )))
                ->addFilter('An')
                ->addFilter('Status', array('values' => array(
                        0 => __b('Fehler'),
                        1 => __b('Ok'),
                        '' => __b('Alle')
                        )));
        $grid->addFilters($filters);

        // option row
        $option = new Bvb_Grid_Extra_Column();
        $option
                ->position('right')
                ->name(__b('Options'))
                ->decorator(
                        '<div>
                    <a href="#" onclick="return popup(\'/administration_email/show/id/{{' . __b('ID') . '}}\', \'Email\', 800, 600);">' . __b('Ansehen') . '</a>
                </div>'
        );
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * Logout
     */
    public function logoutAction() {
        $this->setupLogout();
        $this->_redirect("/");
    }

    /**
     * Login to a company account + redirection
     */
    public function companyloginAction() {
        $company = new Yourdelivery_Model_Company($this->getRequest()->getParam('id'));
        if (is_null($company->getId())) {
            $this->error(__b('This company is non-existent'));
            $this->_redirect('/administration/companys');
        } else {
            foreach ($company->getEmployees() as $empl) {
                if ($empl->isAdmin($company)) {
                    $this->_redirect('/administration/userlogin/id/' . $empl->getId());
                } else {
                    $this->error(__b("You don't have permissions for this company"));
                }
            }
        }
        $this->_redirect('/company');
    }

    /**
     * Login to a restaurant account + redirection
     */
    public function serviceloginAction() {
        $service = new Yourdelivery_Model_Servicetype_Restaurant($this->getRequest()->getParam('id'));
        if (is_null($service->getId())) {
            $this->error(__b('This Restaurant is non-existent'));
            $this->_redirect('/administration/services');
        } else {
            $sessionRestaurant = new Zend_Session_Namespace('Restaurant');
            $sessionRestaurant->currentRestaurant = $service;
            $sessionRestaurant->masterAdmin = $this->session->admin;
            $this->logger->adminInfo(sprintf('Logged to the restaurant %s (%d)', $service->getName(), $service->getId()));
        }

        $this->_redirect('/restaurant');
    }

    /**
     * login to partner backend via admin backend
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 13.06.2012
     */
    public function partnerloginAction(){
        $service = new Yourdelivery_Model_Servicetype_Restaurant($this->getRequest()->getParam('id'));
        if (is_null($service->getId())) {
            $this->error(__b('This Restaurant is non-existent'));
            $this->_redirect('/administration/services');
        } else {
            $sessionRestaurant = new Zend_Session_Namespace('Default');
            $sessionRestaurant->partnerRestaurantId = $service->getId();
            $sessionRestaurant->masterAdmin = $this->session->admin;
            $this->logger->adminInfo(sprintf('Logged to the partner backend of %s (%d)', $service->getName(), $service->getId()));
        }

        $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);

    }

    /*
     * Login to a user account + redirection
     */

    public function userloginAction() {
        $request = $this->getRequest();
        $customerId = $request->getParam('id');

        if ($customerId !== null) {
            try {
                //get frontend session and unset them all first
                //then login as selected user
                $this->session_front->unsetAll();
                $this->session_front->customerId = $customerId;

                $customer = new Yourdelivery_Model_Customer($customerId);
                $customer->login();

                return $this->_redirect($customer->getStartUrl());
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b('Login failed'));
                return $this->_redirect('/administration/users');
            }
        }
        $this->error(__b('No user given'));
        return $this->_redirect('/administration/users');
    }

    /**
     * Login
     * @author vpriem
     * @since 30.11.2010
     */
    public function loginAction() {
        // redirect if already logged in
        if (is_object($this->session->admin)) {
            $this->_redirect('/administration/dashboard');
        }

        if ($this->config->administration->redirect->enabled) {
            $this->_redirect($this->config->administration->redirect->url);
        }

        // get request
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = $request->getParam('user');
            $pass = $request->getParam('pass');

            if ($user === null || $pass === null || (strlen(trim($user)) == 0) || (strlen(trim($pass)) == 0)) {
                $this->warn(__b('No login-data given'));
            } else {
                // insert login values into auth adapter
                $this->adminAuth
                        ->setIdentity($user)
                        ->setCredential($pass);
                // get result ...
                $result = $this->adminAuth->authenticate();

                //... and check it
                switch ($result->getCode()) {
                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND: // do not speak too much
                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->warn(__b('Invalid user or wrong password'));
                        break;

                    case Zend_Auth_Result::SUCCESS:
                        // direct redirect to intern page
                        $result = $this->setupLogin($result->getIdentity());
                        if ($result) {
                            $this->logger->adminInfo(sprintf('Successfully loggged in admin'));
                            $this->_redirect('/administration/dashboard');
                        } else {
                            $this->warn('Login failed');
                            $this->logger->err(sprintf('Could not log admin in ') . $result->getIdentity());
                        }
                        break;

                    default:
                        $this->warn(__b('Unknown error'));
                }
            }
        }
    }

    /**
     * Set the login session by the admin's email
     *
     * @param string $admin
     * @return boolean
     */
    private function setupLogin($admin) {
        if (is_null($this->session->admin)) {
            $this->session->admin = new Yourdelivery_Model_Admin(null, $admin);
            $this->info(__b('Successfullly logged in'));
            return true;
        } else {
            return true;
        }
    }

    /**
     *  Log one admin out
     *
     * @return boolean
     */
    private function setupLogout() {
        if (!is_null($this->session->admin)) {
            $this->session->unsetAll();
            $this->info(__b('Successfullly logged out'));
            return true;
        } else {
            $this->warn(__b('Logout failure'));
            return false;
        }
    }

    /**
     * Error handling
     */
    public function errorAction() {

    }

    /**
     * show/hide deleted users
     */
    public function showdeletedusersAction() {
        $show = $this->getRequest()->getParam('do', 0);

        if ($show) {
            $this->session->showdeletedusers = '1';
        } else {
            $this->session->showdeletedusers = '0';
        }
        $this->_redirect('/administration/users');
    }

    /**
     * show/hide deleted companies
     */
    public function showdeletedcompaniesAction() {
        $show = $this->getRequest()->getParam('do', 0);

        if ($show) {
            $this->session->showdeletedcomp = '1';
        } else {
            $this->session->showdeletedcomp = '0';
        }
        $this->_redirect('/administration/companys');
    }

    /**
     * show/hide deleted services
     */
    public function showdeletedservicesAction() {
        $show = $this->getRequest()->getParam('do', 0);

        if ($show) {
            $this->session->showdeletedservices = '1';
        } else {
            $this->session->showdeletedservices = '0';
        }
        $this->_redirect('/administration/services');
    }

    /**
     * show/hide offline services
     */
    public function showofflineservicesAction() {
        $show = $this->getRequest()->getParam('do', 0);

        if ($show) {
            $this->session->showofflineservices = '1';
            $this->success(__b('Show offline services activated'));
        } else {
            $this->session->showofflineservices = '0';
            $this->success(__b('Show offline services de-activated'));
        }
        $this->_redirect('/administration/services');
    }

    /**
     * change admin backend settings
     */
    public function settingsAction() {
        $this->view->assign('navsettings', 'active');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $showeditime = $this->getRequest()->getParam('showedittime', null);

            if (!is_null($showeditime)) {
                if (strcmp($showeditime, 'show') == 0) {
                    $this->session->show_edit_time = '1';
                } else {
                    $this->session->show_edit_time = '0';
                }
            }
        }
        $this->view->show_edit_time = $this->session->show_edit_time;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function cronjobAction() {
        $this->_disableView(true);
        $xml = new DOMDocument();
        $xml->load(APPLICATION_PATH . '/../cron/status.xml');
        $xslt = new XSLTProcessor();
        $xsl = new DOMDocument();
        $xsl->load(APPLICATION_PATH . '/templates/xsl/cronjobs/status.xsl', LIBXML_NOCDATA);
        $xslt->importStylesheet($xsl);
        echo $xslt->transformToXML($xml);
    }

    /**
     * Switches backend's locale for passed one
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 14.06.2012
     *
     * @return void
     */
    public function setlocaleAction() {
        
        $request = $this->getRequest();
        $lc = $request->getParam('lc');
        
        $this->_overrideLocale(null, $lc);
        
        // coming back (assuming, that HTTP referer is internal)        
        $referer = Default_Helpers_Web::getReferer(false);
        $this->_redirect($referer == 'UNKNOWN' ? '/administration' : $referer);
    }

    /**
     * Show static page with definitions to springer tasks
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.06.2012
     *
     * @return void
     */
    public function definitionsAction() {
        $developer = array(
            'namba@janamesa.com.br'

        );

        $admin = $this->session->admin;
        $email = $admin->getEmail();

        if (in_array($admin->getEmail(), $developer)) {
            $this->view->isdeveloper = true;
        }
    }

}
